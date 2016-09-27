<?php
/**
 * Piwik - free/libre analytics platform.
 *
 * @link http://piwik.org
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MonitorLizard\Reports;

use Piwik\Columns\Dimension;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Actions\Columns\Metrics\AveragePageGenerationTime;
use Piwik\Plugins\Actions\Columns\Metrics\ExitRate;
use Piwik\Plugins\CoreHome\Columns\Metrics\BounceRate;
use Piwik\Plugins\CustomDimensions\Columns\Metrics\AverageTimeOnDimension;
use Piwik\Plugins\CustomDimensions\Dimension\CustomActionDimension;

/**
 * This class defines a new report.
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetActionsByUser extends Report
{
    protected function init()
    {
        parent::init();

        $this->category = 'General_Actions';
        $this->menuTitle = 'MonitorLizard_ActionsByUser';
        $this->name = Piwik::translate('MonitorLizard_ActionsByUser');
        $this->order = 200;
        $this->actionToLoadSubTables = $this->action;

        $idDimension = 1;
        $idSite = Common::getRequestVar('idSite', 0, 'int');

        $this->initThisReportFromDimension($dimension);
    }

    public function configureView(ViewDataTable $view)
    {
        $idDimension = Common::getRequestVar('idDimension', 0, 'int');
        if ($idDimension < 1) {
            return;
        }

        $isWidget = Common::getRequestVar('widget', 0, 'int');
        $module = Common::getRequestVar('module', '', 'string');
        if ($isWidget && $module !== 'Widgetize' && $view->isViewDataTableId(HtmlTable::ID)) {
            // we disable row evolution as it would not forward the idDimension when requesting the row evolution
        // this is a limitation in row evolution
        $view->config->disable_row_evolution = true;
        }

        $module = $view->requestConfig->getApiModuleToRequest();
        $method = $view->requestConfig->getApiMethodToRequest();
        $idReport = sprintf('%s_%s_idDimension--%d', $module, $method, $idDimension);

        if ($view->requestConfig->idSubtable) {
            $view->config->addTranslation('label', Piwik::translate('Actions_ColumnActionURL'));
        } elseif (!empty($this->dimension)) {
            $view->config->addTranslation('label', $this->dimension->getName());
        }

        $view->requestConfig->request_parameters_to_modify['idDimension'] = $idDimension;
        $view->requestConfig->request_parameters_to_modify['reportUniqueId'] = $idReport;
        $view->config->custom_parameters['scopeOfDimension'] = 'action';

        $view->config->columns_to_display = array(
        'label', 'nb_hits', 'nb_visits', 'bounce_rate', 'avg_time_on_dimension', 'exit_rate', 'avg_time_generation',
      );

        $formatter = new \Piwik\Metrics\Formatter();

      // add avg_generation_time tooltip
      $tooltipCallback = function ($hits, $min, $max) use ($formatter) {
          if (!$hits) {
              return false;
          }

          return Piwik::translate('Actions_AvgGenerationTimeTooltip', array(
          $hits,
          '<br />',
          $formatter->getPrettyTimeFromSeconds($min, true),
          $formatter->getPrettyTimeFromSeconds($max, true),
        ));
      };
        $view->config->filters[] = array('ColumnCallbackAddMetadata',
        array(
          array('nb_hits_with_time_generation', 'min_time_generation', 'max_time_generation'),
          'avg_time_generation_tooltip',
          $tooltipCallback,
        ),
      );

        $view->config->show_table_all_columns = false;
    }

    private function initThisReportFromDimension($dimension)
    {
        $dimensionField = 'dimension1';

        $this->dimension = new CustomActionDimension($dimensionField, 'User name');
        $this->metrics = array('nb_hits', 'nb_visits');
        $this->processedMetrics = array(
      new AverageTimeOnDimension(),
      new BounceRate(),
      new ExitRate(),
      new AveragePageGenerationTime(),
    );

        $this->parameters = array('idDimension' => 1);

        return true;
    }
}
