<?php
/**
 * Piwik - free/libre analytics platform.
 *
 * @link http://piwik.org
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MonitorLizard\Reports;

use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Actions\Columns\PageTitle;
use Piwik\Plugins\CustomDimensions\Dimension\CustomActionDimension;
use Piwik\Plugins\UserId\Columns\UserId;

/**
 * This class defines a new report.
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetActionsByUser extends Report
{
    protected function init()
    {
        $this->category = 'General_Actions';
        $this->name = Piwik::translate('MonitorLizard_ActionsByUser');
        $this->dimension = new CustomActionDimension(2, 'User name');
        $this->documentation = Piwik::translate('');

        // This defines in which order your report appears in the mobile app, in the menu and in the list of widgets
        $this->order = 200;

        // Uncomment the next line if your report should be able to load subtables. You can define any action here
        $this->actionToLoadSubTables = 'getActionsForUser';

        // Uncomment the next line if your report always returns a constant count of rows, for instance always
        // 24 rows for 1-24hours
        // $this->constantRowsCount = true;

        // If a menu title is specified, the report will be displayed in the menu
         $this->menuTitle = 'MonitorLizard_ActionsByUser';
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->show_flatten_table = FALSE;
    }
}
