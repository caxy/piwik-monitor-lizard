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
use Piwik\Piwik;
use Piwik\Plugins\CustomDimensions\Dimension\Dimension;

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
        $idDimension = 1;
        Piwik::checkUserHasViewAccess($idSite);

        $dimension = new Dimension($idDimension, $idSite);
        $dimension->checkActive();

        $record = 'MonitorLizard_pagesByUser';

        $dataTable = Archive::createDataTableFromArchive($record, $idSite, $period, $date, $segment, $expanded, $flat, $idSubtable);

//      if (isset($idSubtable) && $dataTable->getRowsCount()) {
//        $parentTable = Archive::createDataTableFromArchive($record, $idSite, $period, $date, $segment);
//        foreach ($parentTable->getRows() as $row) {
//          if ($row->getIdSubDataTable() == $idSubtable) {
//            $parentValue = $row->getColumn('label');
//            $dataTable->queueFilter('Piwik\Plugins\CustomDimensions\DataTable\Filter\AddSubtableSegmentMetadata', array($idDimension, $parentValue));
//            break;
//          }
//        }
//      } else {
//        $dataTable->queueFilter('Piwik\Plugins\CustomDimensions\DataTable\Filter\AddSegmentMetadata', array($idDimension));
//      }

//      $dataTable->filter('Piwik\Plugins\CustomDimensions\DataTable\Filter\RemoveUserIfNeeded', array($idSite, $period, $date));

      return $dataTable;
    }
}
