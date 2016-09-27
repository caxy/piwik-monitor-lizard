<?php

namespace Piwik\Plugins\MonitorLizard;

use Piwik\Config;
use Piwik\Metrics;
use Piwik\Plugins\CustomDimensions\Dao\LogTable;
use Piwik\Plugins\CustomDimensions\DataArray;

class Archiver extends \Piwik\Plugin\Archiver
{
    const LABEL_CUSTOM_VALUE_NOT_DEFINED = 'Value not defined';

  /** @var DataArray */
  protected $dataArray;
    protected $maximumRowsInDataTableLevelZero;
    protected $maximumRowsInSubDataTable;

    public function __construct($processor)
    {
        parent::__construct($processor);
        $this->maximumRowsInDataTableLevelZero = Config::getInstance()->General['datatable_archiving_maximum_rows_custom_variables'];
        $this->maximumRowsInSubDataTable = Config::getInstance()->General['datatable_archiving_maximum_rows_subtable_custom_variables'];
    }

    public function aggregateMultipleReports()
    {
        $columnsAggregationOperation = null;

        $this->getProcessor()->aggregateDataTableRecords(
      ['MonitorLizard_pagesByUser'],
      $this->maximumRowsInDataTableLevelZero,
      $this->maximumRowsInSubDataTable,
      $columnToSort = Metrics::INDEX_NB_VISITS,
      $columnsAggregationOperation,
      $columnsToRenameAfterAggregation = null,
      $countRowsRecursive = array());
    }

    public function aggregateDayReport()
    {
        $this->dataArray = new DataArray();

        $valueField = LogTable::buildCustomDimensionColumnName(['index' => 1]);

        $this->aggregateFromActions($valueField);

//    $this->dataArray->enrichMetricsWithConversions();
    $table = $this->dataArray->asDataTable();

        $blob = $table->getSerialized(
      $this->maximumRowsInDataTableLevelZero, $this->maximumRowsInSubDataTable,
      $columnToSort = Metrics::INDEX_NB_VISITS
    );

        $recordName = 'MonitorLizard_pagesByUser';
        $this->getProcessor()->insertBlobRecord($recordName, $blob);
    }

    protected function aggregateFromActions($valueField)
    {
        $resultSet = $this->queryCustomDimensionActions($this->dataArray, $valueField);

        while ($row = $resultSet->fetch()) {
            // make sure we always work with normalized URL no matter how the individual action stores it
      $normalized = \Piwik\Tracker\PageUrl::normalizeUrl($row['url']);
            $row['url'] = $normalized['url'];

            $label = $row['url'];
            $this->dataArray->sumMetricsActions($label, $row);

            $subLabel = $row[$valueField];
            $subLabel = $this->cleanCustomDimensionValue($subLabel);

            if (empty($subLabel)) {
                continue;
            }

            $this->dataArray->sumMetricsActionCustomDimensionsPivot($label, $subLabel, $row);
        }
    }

    public function queryCustomDimensionActions(DataArray $dataArray, $valueField, $additionalWhere = '')
    {
        $metricsConfig = \Piwik\Plugins\Actions\Metrics::getActionMetrics();

        $metricIds = array_keys($metricsConfig);
        $metricIds[] = Metrics::INDEX_PAGE_SUM_TIME_SPENT;
        $metricIds[] = Metrics::INDEX_BOUNCE_COUNT;
        $metricIds[] = Metrics::INDEX_PAGE_EXIT_NB_VISITS;
        $dataArray->setActionMetricsIds($metricIds);

        $select = "log_link_visit_action.$valueField,
                  log_action.name as url,
                  sum(log_link_visit_action.time_spent) as `".Metrics::INDEX_PAGE_SUM_TIME_SPENT.'`,
                  sum(case log_visit.visit_total_actions when 1 then 1 when 0 then 1 else 0 end) as `' .Metrics::INDEX_BOUNCE_COUNT.'`,
                  sum(IF(log_visit.last_idlink_va = log_link_visit_action.idlink_va, 1, 0)) as `' .Metrics::INDEX_PAGE_EXIT_NB_VISITS.'`';

        $select = $this->addMetricsToSelect($select, $metricsConfig);

        $from = array(
      'log_link_visit_action',
      array(
        'table' => 'log_visit',
        'joinOn' => 'log_visit.idvisit = log_link_visit_action.idvisit',
      ),
      array(
        'table' => 'log_action',
        'joinOn' => 'log_link_visit_action.idaction_url = log_action.idaction',
      ),
    );

        $where = 'log_link_visit_action.server_time >= ?
                  AND log_link_visit_action.server_time <= ?
                  AND log_link_visit_action.idsite = ?';

        if (!empty($additionalWhere)) {
            $where .= ' AND '.$additionalWhere;
        }

        $groupBy = "url, log_link_visit_action.$valueField";
        $orderBy = '`'.Metrics::INDEX_PAGE_NB_HITS.'` DESC';

    // get query with segmentation
    $logAggregator = $this->getLogAggregator();
        $query = $logAggregator->generateQuery($select, $from, $where, $groupBy, $orderBy);
        error_log($query['sql']);
        $db = $logAggregator->getDb();
        $resultSet = $db->query($query['sql'], $query['bind']);

        return $resultSet;
    }

    private function addMetricsToSelect($select, $metricsConfig)
    {
        if (!empty($metricsConfig)) {
            foreach ($metricsConfig as $metric => $config) {
                $select .= ', '.$config['query'].' as `'.$metric.'`';
            }
        }

        return $select;
    }

    protected function cleanCustomDimensionValue($value)
    {
        if (isset($value) && strlen($value)) {
            return $value;
        }

        return self::LABEL_CUSTOM_VALUE_NOT_DEFINED;
    }
}
