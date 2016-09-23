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
        $dataTable = Archive::createDataTableFromArchive('Actions_actions_url', $idSite, $period, $date, $segment, $expanded, $flat, $idSubtable, $depth);

        return $dataTable;
    }

    /**
     * @param $idSite
     * @param $period
     * @param $date
     * @param bool $segment
     *
     * @return DataTable
     */
    public function getActionsForUser($idSite, $period, $date, $segment = false)
    {
        $instance = \Piwik\Plugins\UserId\API::getInstance();

        return $instance->getUsers($idSite, $period, $date, $segment);
    }
}
