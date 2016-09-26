<?php
/**
 * Piwik - free/libre analytics platform.
 *
 * @link http://piwik.org
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MonitorLizard;

use Piwik\Archive;
use Piwik\DataTable;

/**
 * API for plugin MonitorLizard.
 *
 * @method static \Piwik\Plugins\MonitorLizard\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Another example method that returns a data table.
     *
     * @param int         $idSite
     * @param string      $period
     * @param string      $date
     * @param bool|string $segment
     * @param bool        $expanded
     * @param bool        $idSubtable
     * @param bool        $flat
     *
     * @return DataTable
     */
    public function getActionsByUser($idSite, $period, $date, $segment = false, $expanded = false, $idSubtable = false, $depth = false, $flat = false)
    {
        /** @var DataTable $report */
        $report = \Piwik\API\Request::processRequest('Actions.getPageUrls', array(
          'idSite' => $idSite,
          'period' => $period,
          'date'   => $date,
        ));

        $report->filter(function (DataTable $dataTable) use ($idSite, $period, $date, $segment, $expanded, $idSubtable, $depth, $flat) {
            /** @var DataTable\Row $row */
            foreach ($dataTable->getRows() as $row) {
                $row->setSubtable($this->getUsers($idSite, $period, $date, $row->getMetadata('segment')));
            }
        });


//        $report->filter('AddSegmentByLabel', array('PageUrl'));
//        $report->filter('AddSegmentByLabel', array('PageUrl'));

        return $report;
    }

    /**
     * @param $idSite
     * @param $period
     * @param $date
     * @param bool $segment
     *
     * @return DataTable
     */
    public function getUsers($idSite, $period, $date, $segment = false, $idSubtable = false)
    {
        dump(func_get_args());
        $report = \Piwik\API\Request::processRequest('CustomDimensions.getCustomDimension', array(
          'idSite' => $idSite,
          'period' => $period,
          'date'   => $date,
          'segment' => $segment,
          'idSubtable' => $idSubtable,
          'idDimension' => 2,
        ));

        return $report;
    }
}
