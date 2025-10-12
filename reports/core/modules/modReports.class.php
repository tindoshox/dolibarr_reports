<?php
/* Copyright (C) 2004-2018  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019  Nicolas ZABOURI         <info@inovea-conseil.com>
 * Copyright (C) 2019-2020  Frédéric France         <frederic.france@netlogic.fr>
 * Copyright (C) 2022 Admin SUPER <support@smbi.co.za>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * 	\defgroup   reports     Module Reports
 *  \brief      Reports module descriptor.
 *
 *  \file       htdocs/reports/core/modules/modReports.class.php
 *  \ingroup    reports
 *  \brief      Description and activation file for module Reports
 */
include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 *  Description and activation class for module Reports.
 */
class modReports extends DolibarrModules
{
    /**
     * Constructor. Define names, constants, directories, boxes, permissions.
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        global $langs, $conf;
        $this->db = $db;

        // Id for module (must be unique).
        // Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
        $this->numero = 5800000; // TODO Go on page https://wiki.dolibarr.org/index.php/List_of_modules_id to reserve an id number for your module

        // Key text used to identify module (for permissions, menus, etc...)
        $this->rights_class = 'reports';

        // Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
        // It is used to group modules by family in module setup page
        $this->family = 'crm';

        // Module position in the family on 2 digits ('01', '10', '20', ...)
        $this->module_position = '70';

        // Gives the possibility for the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
        //$this->familyinfo = array('myownfamily' => array('position' => '01', 'label' => $langs->trans("MyOwnFamily")));
        // Module label (no space allowed), used if translation string 'ModuleReportsName' not found (Reports is name of module).
        $this->name = preg_replace('/^mod/i', '', get_class($this));

        // Module description, used if translation string 'ModuleReportsDesc' not found (Reports is name of module).
        $this->description = 'ReportsDescription';
        // Used only if file README.md and README-LL.md not found.
        $this->descriptionlong = 'ReportsDescription';

        // Author
        $this->editor_name = 'Tendai Shoko';
        $this->editor_url = 'https://smbi.co.za';

        // Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
        $this->version = '2.1';
        // Url to the file with your last numberversion of this module
        //$this->url_last_version = 'http://www.example.com/versionmodule.txt';

        // Key used in llx_const table to save module status enabled/disabled (where REPORTS is value of property name of module in uppercase)
        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);

        // Name of image file used for this module.
        // If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
        // If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
        // To use a supported fa-xxx css style of font awesome, use this->picto='xxx'
        $this->picto = 'fas fa-file-pdf';

        // Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
        $this->module_parts = [
            // Set this to 1 if module has its own trigger directory (core/triggers)
            'triggers' => 0,
            // Set this to 1 if module has its own login method file (core/login)
            'login' => 0,
            // Set this to 1 if module has its own substitution function file (core/substitutions)
            'substitutions' => 0,
            // Set this to 1 if module has its own menus handler directory (core/menus)
            'menus' => 0,
            // Set this to 1 if module overwrite template dir (core/tpl)
            'tpl' => 0,
            // Set this to 1 if module has its own barcode directory (core/modules/barcode)
            'barcode' => 0,
            // Set this to 1 if module has its own models directory (core/modules/xxx)
            'models' => 1,
            // Set this to 1 if module has its own printing directory (core/modules/printing)
            'printing' => 0,
            // Set this to 1 if module has its own theme directory (theme)
            'theme' => 0,
            // Set this to relative path of css file if module has its own css file
            'css' => [
                '/reports/css/reports.css.php',
            ],
            // Set this to relative path of js file if module must load a js on all pages
            'js' => [
                // '/reports/js/reports.js.php',
            ],
            // Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context to 'all'
            'hooks' => [
                //   'data' => array(
                //       'hookcontext1',
                //       'hookcontext2',
                //   ),
                //   'entity' => '0',
            ],
            // Set this to 1 if features of module are opened to external users
            'moduleforexternal' => 0,
        ];

        // Data directories to create when module is enabled.
        // Example: this->dirs = array("/reports/temp","/reports/subdir");
        $this->dirs = ['/reports/temp'];

        // Config pages. Put here list of php page, stored into reports/admin directory, to use to setup module.
        $this->config_page_url = ['setup.php@reports'];

        // Dependencies
        // A condition to hide module
        $this->hidden = false;
        // List of module class names as string that must be enabled if this module is enabled. Example: array('always1'=>'modModuleToEnable1','always2'=>'modModuleToEnable2', 'FR1'=>'modModuleToEnableFR'...)
        $this->depends = [];
        $this->requiredby = []; // List of module class names as string to disable if this one is disabled. Example: array('modModuleToDisable1', ...)
        $this->conflictwith = []; // List of module class names as string this module is in conflict with. Example: array('modModuleToDisable1', ...)

        // The language file dedicated to your module
        $this->langfiles = ['reports@reports'];

        // Prerequisites
        $this->phpmin = [5, 6]; // Minimum version of PHP required by module
        $this->need_dolibarr_version = [11, -3]; // Minimum version of Dolibarr required by module

        // Messages at activation
        $this->warnings_activation = []; // Warning to show when we activate module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
        $this->warnings_activation_ext = []; // Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
        //$this->automatic_activation = array('FR'=>'ReportsWasAutomaticallyActivatedBecauseOfYourCountryChoice');
        //$this->always_enabled = true;								// If true, can't be disabled

        // Constants
        // List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
        // Example: $this->const=array(1 => array('REPORTS_MYNEWCONST1', 'chaine', 'myvalue', 'This is a constant to add', 1),
        //                             2 => array('REPORTS_MYNEWCONST2', 'chaine', 'myvalue', 'This is another constant to add', 0, 'current', 1)
        // );

        $this->const = [];

        // Some keys to add into the overwriting translation tables
        /*$this->overwrite_translation = array(
            'en_US:ParentCompany'=>'Parent company or reseller',
            'fr_FR:ParentCompany'=>'Maison mère ou revendeur'
        )*/

        if (!isset($conf->reports) || !isset($conf->reports->enabled)) {
            $conf->reports = new stdClass();
            $conf->reports->enabled = 0;
        }

        // Array to add new pages in new tabs
        $this->tabs = [];
        // Example:
        // $this->tabs[] = array('data'=>'objecttype:+tabname1:Title1:mylangfile@reports:$user->rights->reports->read:/reports/mynewtab1.php?id=__ID__');  					// To add a new tab identified by code tabname1
        // $this->tabs[] = array('data'=>'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@reports:$user->rights->othermodule->read:/reports/mynewtab2.php?id=__ID__',  	// To add another new tab identified by code tabname2. Label will be result of calling all substitution functions on 'Title2' key.
        // $this->tabs[] = array('data'=>'objecttype:-tabname:NU:conditiontoremove');                                                     										// To remove an existing tab identified by code tabname
        //
        // Where objecttype can be
        // 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
        // 'contact'          to add a tab in contact view
        // 'contract'         to add a tab in contract view
        // 'group'            to add a tab in group view
        // 'intervention'     to add a tab in intervention view
        // 'invoice'          to add a tab in customer invoice view
        // 'invoice_supplier' to add a tab in supplier invoice view
        // 'member'           to add a tab in fundation member view
        // 'opensurveypoll'	  to add a tab in opensurvey poll view
        // 'order'            to add a tab in customer order view
        // 'order_supplier'   to add a tab in supplier order view
        // 'payment'		  to add a tab in payment view
        // 'payment_supplier' to add a tab in supplier payment view
        // 'product'          to add a tab in product view
        // 'propal'           to add a tab in propal view
        // 'project'          to add a tab in project view
        // 'stock'            to add a tab in stock view
        // 'thirdparty'       to add a tab in third party view
        // 'user'             to add a tab in user view

        // Dictionaries
        $this->dictionaries = [];
        /* Example:
        $this->dictionaries=array(
            'langs'=>'reports@reports',
            // List of tables we want to see into dictonnary editor
            'tabname'=>array(MAIN_DB_PREFIX."table1", MAIN_DB_PREFIX."table2", MAIN_DB_PREFIX."table3"),
            // Label of tables
            'tablib'=>array("Table1", "Table2", "Table3"),
            // Request to select fields
            'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table1 as f', 'SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table2 as f', 'SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table3 as f'),
            // Sort order
            'tabsqlsort'=>array("label ASC", "label ASC", "label ASC"),
            // List of fields (result of select to show dictionary)
            'tabfield'=>array("code,label", "code,label", "code,label"),
            // List of fields (list of fields to edit a record)
            'tabfieldvalue'=>array("code,label", "code,label", "code,label"),
            // List of fields (list of fields for insert)
            'tabfieldinsert'=>array("code,label", "code,label", "code,label"),
            // Name of columns with primary key (try to always name it 'rowid')
            'tabrowid'=>array("rowid", "rowid", "rowid"),
            // Condition to show each dictionary
            'tabcond'=>array($conf->reports->enabled, $conf->reports->enabled, $conf->reports->enabled)
        );
        */

        // Boxes/Widgets
        // Add here list of php file(s) stored in reports/core/boxes that contains a class to show a widget.
        $this->boxes = [
            //  0 => array(
            //      'file' => 'reportswidget1.php@reports',
            //      'note' => 'Widget provided by Reports',
            //      'enabledbydefaulton' => 'Home',
            //  ),
            //  ...
        ];

        // Cronjobs (List of cron jobs entries to add when module is enabled)
        // unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
        $this->cronjobs = [
            //  0 => array(
            //      'label' => 'MyJob label',
            //      'jobtype' => 'method',
            //      'class' => '/reports/class/myobject_list.class.php',
            //      'objectname' => 'Myobject_list',
            //      'method' => 'doScheduledJob',
            //      'parameters' => '',
            //      'comment' => 'Comment',
            //      'frequency' => 2,
            //      'unitfrequency' => 3600,
            //      'status' => 0,
            //      'reports' => '$conf->reports->enabled',
            //      'priority' => 50,
            //  ),
        ];
        // Example: $this->cronjobs=array(
        //    0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'/dir/class/file.class.php', 'objectname'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'reports'=>'$conf->reports->enabled', 'priority'=>50),
        //    1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600*24, 'status'=>0, 'reports'=>'$conf->reports->enabled', 'priority'=>50)
        // );

        // Permissions provided by this module
        $this->rights = [];
        $r = 0;
        // Add here entries to declare new permissions
        /* BEGIN MODULEBUILDER PERMISSIONS */
        $this->rights[$r][0] = $this->numero.sprintf('%02d', $r + 1); // Permission id (must not be already used)
        $this->rights[$r][1] = 'Read objects of Reports'; // Permission label
        $this->rights[$r][4] = 'myobject_list';
        $this->rights[$r][5] = 'read'; // In php code, permission will be checked by reports if ($user->rights->reports->myobject_list->read)
        ++$r;
        $this->rights[$r][0] = $this->numero.sprintf('%02d', $r + 1); // Permission id (must not be already used)
        $this->rights[$r][1] = 'Create/Update objects of Reports'; // Permission label
        $this->rights[$r][4] = 'myobject_list';
        $this->rights[$r][5] = 'write'; // In php code, permission will be checked by reports if ($user->rights->reports->myobject_list->write)
        ++$r;
        $this->rights[$r][0] = $this->numero.sprintf('%02d', $r + 1); // Permission id (must not be already used)
        $this->rights[$r][1] = 'Delete objects of Reports'; // Permission label
        $this->rights[$r][4] = 'myobject_list';
        $this->rights[$r][5] = 'delete'; // In php code, permission will be checked by reports if ($user->rights->reports->myobject_list->delete)
        ++$r;

        /* END MODULEBUILDER PERMISSIONS */

        // Main menu entries to add
        $this->menu = [];
        $a = 0;
        $r = 0;
        $menuArray = [
		['Receipts (Collection)', 'receipts'],
        ['Receipts Compact', 'receipts_mobile'],
        ['Balances', 'balances'],
        ['Daily Totals', 'daily_totals'],
        ['Credit Notes (Returns)', 'returns'],
        ['Monthly Totals', 'monthly_totals'],
        ['Overdue', 'overdue'],
        ['Percentage Collection', 'percentage'],
        ['Receipt Book Check', 'receiptbook'],
        ['Sales', 'sales'],
        ['Stock Balance (Stock Count)', 'stock'],
        ['Open Invoices', 'open'],
        ['Unclassified Invoices', 'unclassified'],
        ];
        // Add here entries to declare new menus
        /* BEGIN MODULEBUILDER TOPMENU */
        $this->menu[$r++] = [
            'fk_menu' => '', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'type' => 'top', // This is a Top menu entry
            'titre' => 'Reports',
            'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle "'),
            'mainmenu' => 'reports',
            'leftmenu' => '',
            'url' => '/reports/reportsindex.php',
            'langs' => 'reports@reports', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'position' => 1000 + $r,
            'enabled' => '$conf->reports->enabled', // Define condition to show or hide menu entry. Use '$conf->reports->enabled' if entry must be visible if module is enabled.
            'perms' => '1', // Use 'perms'=>'$user->rights->reports->myobject_list->read' if you want your menu with a permission rules
            'target' => '',
            'user' => 0, // 0=Menu for internal users, 1=external users, 2=both
        ];
        /* END MODULEBUILDER TOPMENU */
        /* BEGIN MODULEBUILDER LEFTMENU myobject_list*/
        $this->menu[$r++] = [
            'fk_menu' => 'fk_mainmenu=reports',      // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'type' => 'left',                          // This is a Top menu entry
            'titre' => 'Overview',
            'prefix' => '<span class="far fa-file-pdf paddingright pictofixedwidth valignmiddle"></span>',
            'mainmenu' => 'reports',
            'leftmenu' => 'myobject_list',
            'url' => '/reports/reportsindex.php',
            'langs' => 'reports@reports',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'position' => 1000 + $r,
            'enabled' => '$conf->reports->enabled',  // Define condition to show or hide menu entry. Use '$conf->reports->enabled' if entry must be visible if module is enabled.
            'perms' => '$user->rights->reports->myobject_list->read',			                // Use 'perms'=>'$user->rights->reports->level1->level2' if you want your menu with a permission rules
            'target' => '',
            'user' => 2,				                // 0=Menu for internal users, 1=external users, 2=both
        ];

        $this->menu[$r++] = [
            'fk_menu' => 'fk_mainmenu=reports,fk_leftmenu=myobject_list',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'type' => 'left',			                // This is a Left menu entry
            'titre' => $menuArray[0][0],
            'mainmenu' => 'reports',
            'leftmenu' => 'reports',
            'url' => '/reports/selection.php?reportid='.$menuArray[0][1],
            'langs' => 'reports@reports',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'position' => 1000 + $r,
            'enabled' => '$conf->reports->enabled',  // Define condition to show or hide menu entry. Use '$conf->reports->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'perms' => '$user->rights->reports->myobject_list->delete',			                // Use 'perms'=>'$user->rights->reports->level1->level2' if you want your menu with a permission rules
            'target' => '',
            'user' => 2,				                // 0=Menu for internal users, 1=external users, 2=both
        ];

        $this->menu[$r++] = [
            'fk_menu' => 'fk_mainmenu=reports,fk_leftmenu=myobject_list',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'type' => 'left',			                // This is a Left menu entry
            'titre' => $menuArray[1][0],
            'mainmenu' => 'reports',
            'leftmenu' => 'reports',
            'url' => '/reports/selection.php?reportid='.$menuArray[1][1],
            'langs' => 'reports@reports',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'position' => 1000 + $r,
            'enabled' => '$conf->reports->enabled',  // Define condition to show or hide menu entry. Use '$conf->reports->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'perms' => '$user->rights->reports->myobject_list->delete',			                // Use 'perms'=>'$user->rights->reports->level1->level2' if you want your menu with a permission rules
            'target' => '',
            'user' => 2,				                // 0=Menu for internal users, 1=external users, 2=both
        ];
        $this->menu[$r++] = [
            'fk_menu' => 'fk_mainmenu=reports,fk_leftmenu=myobject_list',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'type' => 'left',			                // This is a Left menu entry
            'titre' => $menuArray[2][0],
            'mainmenu' => 'reports',
            'leftmenu' => 'reports',
            'url' => '/reports/selection.php?reportid='.$menuArray[2][1],
            'langs' => 'reports@reports',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'position' => 1000 + $r,
            'enabled' => '$conf->reports->enabled',  // Define condition to show or hide menu entry. Use '$conf->reports->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'perms' => '$user->rights->reports->myobject_list->delete',			                // Use 'perms'=>'$user->rights->reports->level1->level2' if you want your menu with a permission rules
            'target' => '',
            'user' => 2,				                // 0=Menu for internal users, 1=external users, 2=both
        ];

        $this->menu[$r++] = [
            'fk_menu' => 'fk_mainmenu=reports,fk_leftmenu=myobject_list',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'type' => 'left',			                // This is a Left menu entry
            'titre' => $menuArray[3][0],
            'mainmenu' => 'reports',
            'leftmenu' => 'reports',
            'url' => '/reports/selection.php?reportid='.$menuArray[3][1],
            'langs' => 'reports@reports',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'position' => 1000 + $r,
            'enabled' => '$conf->reports->enabled',  // Define condition to show or hide menu entry. Use '$conf->reports->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'perms' => '$user->rights->reports->myobject_list->delete',			                // Use 'perms'=>'$user->rights->reports->level1->level2' if you want your menu with a permission rules
            'target' => '',
            'user' => 2,				                // 0=Menu for internal users, 1=external users, 2=both
        ];

        $this->menu[$r++] = [
            'fk_menu' => 'fk_mainmenu=reports,fk_leftmenu=myobject_list',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'type' => 'left',			                // This is a Left menu entry
            'titre' => $menuArray[4][0],
            'mainmenu' => 'reports',
            'leftmenu' => 'reports',
            'url' => '/reports/selection.php?reportid='.$menuArray[4][1],
            'langs' => 'reports@reports',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'position' => 1000 + $r,
            'enabled' => '$conf->reports->enabled',  // Define condition to show or hide menu entry. Use '$conf->reports->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'perms' => '$user->rights->reports->myobject_list->delete',			                // Use 'perms'=>'$user->rights->reports->level1->level2' if you want your menu with a permission rules
            'target' => '',
            'user' => 2,				                // 0=Menu for internal users, 1=external users, 2=both
        ];

        $this->menu[$r++] = [
            'fk_menu' => 'fk_mainmenu=reports,fk_leftmenu=myobject_list',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'type' => 'left',			                // This is a Left menu entry
            'titre' => $menuArray[5][0],
            'mainmenu' => 'reports',
            'leftmenu' => 'reports',
            'url' => '/reports/selection.php?reportid='.$menuArray[5][1],
            'langs' => 'reports@reports',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'position' => 1000 + $r,
            'enabled' => '$conf->reports->enabled',  // Define condition to show or hide menu entry. Use '$conf->reports->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'perms' => '$user->rights->reports->myobject_list->delete',			                // Use 'perms'=>'$user->rights->reports->level1->level2' if you want your menu with a permission rules
            'target' => '',
            'user' => 2,				                // 0=Menu for internal users, 1=external users, 2=both
        ];

        $this->menu[$r++] = [
            'fk_menu' => 'fk_mainmenu=reports,fk_leftmenu=myobject_list',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'type' => 'left',			                // This is a Left menu entry
            'titre' => $menuArray[6][0],
            'mainmenu' => 'reports',
            'leftmenu' => 'reports',
            'url' => '/reports/selection.php?reportid='.$menuArray[6][1],
            'langs' => 'reports@reports',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'position' => 1000 + $r,
            'enabled' => '$conf->reports->enabled',  // Define condition to show or hide menu entry. Use '$conf->reports->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'perms' => '$user->rights->reports->myobject_list->delete',			                // Use 'perms'=>'$user->rights->reports->level1->level2' if you want your menu with a permission rules
            'target' => '',
            'user' => 2,				                // 0=Menu for internal users, 1=external users, 2=both
        ];

        $this->menu[$r++] = [
            'fk_menu' => 'fk_mainmenu=reports,fk_leftmenu=myobject_list',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'type' => 'left',			                // This is a Left menu entry
            'titre' => $menuArray[7][0],
            'mainmenu' => 'reports',
            'leftmenu' => 'reports',
            'url' => '/reports/selection.php?reportid='.$menuArray[7][1],
            'langs' => 'reports@reports',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'position' => 1000 + $r,
            'enabled' => '$conf->reports->enabled',  // Define condition to show or hide menu entry. Use '$conf->reports->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'perms' => '$user->rights->reports->myobject_list->delete',			                // Use 'perms'=>'$user->rights->reports->level1->level2' if you want your menu with a permission rules
            'target' => '',
            'user' => 2,				                // 0=Menu for internal users, 1=external users, 2=both
        ];

        $this->menu[$r++] = [
            'fk_menu' => 'fk_mainmenu=reports,fk_leftmenu=myobject_list',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'type' => 'left',			                // This is a Left menu entry
            'titre' => $menuArray[8][0],
            'mainmenu' => 'reports',
            'leftmenu' => 'reports',
            'url' => '/reports/selection.php?reportid='.$menuArray[8][1],
            'langs' => 'reports@reports',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'position' => 1000 + $r,
            'enabled' => '$conf->reports->enabled',  // Define condition to show or hide menu entry. Use '$conf->reports->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'perms' => '$user->rights->reports->myobject_list->delete',			                // Use 'perms'=>'$user->rights->reports->level1->level2' if you want your menu with a permission rules
            'target' => '',
            'user' => 2,				                // 0=Menu for internal users, 1=external users, 2=both
        ];

        $this->menu[$r++] = [
            'fk_menu' => 'fk_mainmenu=reports,fk_leftmenu=myobject_list',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'type' => 'left',			                // This is a Left menu entry
            'titre' => $menuArray[9][0],
            'mainmenu' => 'reports',
            'leftmenu' => 'reports',
            'url' => '/reports/selection.php?reportid='.$menuArray[9][1],
            'langs' => 'reports@reports',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'position' => 1000 + $r,
            'enabled' => '$conf->reports->enabled',  // Define condition to show or hide menu entry. Use '$conf->reports->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'perms' => '$user->rights->reports->myobject_list->delete',			                // Use 'perms'=>'$user->rights->reports->level1->level2' if you want your menu with a permission rules
            'target' => '',
            'user' => 2,				                // 0=Menu for internal users, 1=external users, 2=both
        ];

        $this->menu[$r++] = [
            'fk_menu' => 'fk_mainmenu=reports,fk_leftmenu=myobject_list',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'type' => 'left',			                // This is a Left menu entry
            'titre' => $menuArray[10][0],
            'mainmenu' => 'reports',
            'leftmenu' => 'reports',
            'url' => '/reports/selection.php?reportid='.$menuArray[10][1],
            'langs' => 'reports@reports',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'position' => 1000 + $r,
            'enabled' => '$conf->reports->enabled',  // Define condition to show or hide menu entry. Use '$conf->reports->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'perms' => '$user->rights->reports->myobject_list->delete',			                // Use 'perms'=>'$user->rights->reports->level1->level2' if you want your menu with a permission rules
            'target' => '',
            'user' => 2,				                // 0=Menu for internal users, 1=external users, 2=both
        ];

        $this->menu[$r++] = [
            'fk_menu' => 'fk_mainmenu=reports,fk_leftmenu=myobject_list',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'type' => 'left',			                // This is a Left menu entry
            'titre' => $menuArray[11][0],
            'mainmenu' => 'reports',
            'leftmenu' => 'reports',
            'url' => '/reports/selection.php?reportid='.$menuArray[11][1],
            'langs' => 'reports@reports',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'position' => 1000 + $r,
            'enabled' => '$conf->reports->enabled',  // Define condition to show or hide menu entry. Use '$conf->reports->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'perms' => '$user->rights->reports->myobject_list->delete',			                // Use 'perms'=>'$user->rights->reports->level1->level2' if you want your menu with a permission rules
            'target' => '',
            'user' => 2,				                // 0=Menu for internal users, 1=external users, 2=both
        ];

        $this->menu[$r++] = [
            'fk_menu' => 'fk_mainmenu=reports,fk_leftmenu=myobject_list',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'type' => 'left',			                // This is a Left menu entry
            'titre' => $menuArray[12][0],
            'mainmenu' => 'reports',
            'leftmenu' => 'reports',
            'url' => '/reports/selection.php?reportid='.$menuArray[12][1],
            'langs' => 'reports@reports',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'position' => 1000 + $r,
            'enabled' => '$conf->reports->enabled',  // Define condition to show or hide menu entry. Use '$conf->reports->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'perms' => '$user->rights->reports->myobject_list->delete',			                // Use 'perms'=>'$user->rights->reports->level1->level2' if you want your menu with a permission rules
            'target' => '',
            'user' => 2,				                // 0=Menu for internal users, 1=external users, 2=both
        ];

        $this->menu[$r++] = [
            'fk_menu' => 'fk_mainmenu=reports,fk_leftmenu=myobject_list',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'type' => 'left',			                // This is a Left menu entry
            'titre' => $menuArray[13][0],
            'mainmenu' => 'reports',
            'leftmenu' => 'reports',
            'url' => '/reports/selection.php?reportid='.$menuArray[13][1],
            'langs' => 'reports@reports',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'position' => 1000 + $r,
            'enabled' => '$conf->reports->enabled',  // Define condition to show or hide menu entry. Use '$conf->reports->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'perms' => '$user->rights->reports->myobject_list->delete',			                // Use 'perms'=>'$user->rights->reports->level1->level2' if you want your menu with a permission rules
            'target' => '',
            'user' => 2,				                // 0=Menu for internal users, 1=external users, 2=both
        ];


        /* END MODULEBUILDER LEFTMENU myobject_list */
        // Exports profiles provided by this module
        $r = 1;
        /* BEGIN MODULEBUILDER EXPORT myobject_list */
        /*
        $langs->load("reports@reports");
        $this->export_code[$r]=$this->rights_class.'_'.$r;
        $this->export_label[$r]='Myobject_listLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
        $this->export_icon[$r]='myobject_list@reports';
        // Define $this->export_fields_array, $this->export_TypeFields_array and $this->export_entities_array
        $keyforclass = 'Myobject_list'; $keyforclassfile='/reports/class/myobject_list.class.php'; $keyforelement='myobject_list@reports';
        include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
        //$this->export_fields_array[$r]['t.fieldtoadd']='FieldToAdd'; $this->export_TypeFields_array[$r]['t.fieldtoadd']='Text';
        //unset($this->export_fields_array[$r]['t.fieldtoremove']);
        //$keyforclass = 'Myobject_listLine'; $keyforclassfile='/reports/class/myobject_list.class.php'; $keyforelement='myobject_listline@reports'; $keyforalias='tl';
        //include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
        $keyforselect='myobject_list'; $keyforaliasextra='extra'; $keyforelement='myobject_list@reports';
        include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
        //$keyforselect='myobject_listline'; $keyforaliasextra='extraline'; $keyforelement='myobject_listline@reports';
        //include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
        //$this->export_dependencies_array[$r] = array('myobject_listline'=>array('tl.rowid','tl.ref')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
        //$this->export_special_array[$r] = array('t.field'=>'...');
        //$this->export_examplevalues_array[$r] = array('t.field'=>'Example');
        //$this->export_help_array[$r] = array('t.field'=>'FieldDescHelp');
        $this->export_sql_start[$r]='SELECT DISTINCT ';
        $this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'myobject_list as t';
        //$this->export_sql_end[$r]  =' LEFT JOIN '.MAIN_DB_PREFIX.'myobject_list_line as tl ON tl.fk_myobject_list = t.rowid';
        $this->export_sql_end[$r] .=' WHERE 1 = 1';
        $this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('myobject_list').')';
        $r++; */
        /* END MODULEBUILDER EXPORT myobject_list */

        // Imports profiles provided by this module
        $r = 1;
        /* BEGIN MODULEBUILDER IMPORT myobject_list */
        /*
         $langs->load("reports@reports");
         $this->export_code[$r]=$this->rights_class.'_'.$r;
         $this->export_label[$r]='Myobject_listLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
         $this->export_icon[$r]='myobject_list@reports';
         $keyforclass = 'Myobject_list'; $keyforclassfile='/reports/class/myobject_list.class.php'; $keyforelement='myobject_list@reports';
         include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
         $keyforselect='myobject_list'; $keyforaliasextra='extra'; $keyforelement='myobject_list@reports';
         include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
         //$this->export_dependencies_array[$r]=array('mysubobject'=>'ts.rowid', 't.myfield'=>array('t.myfield2','t.myfield3')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
         $this->export_sql_start[$r]='SELECT DISTINCT ';
         $this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'myobject_list as t';
         $this->export_sql_end[$r] .=' WHERE 1 = 1';
         $this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('myobject_list').')';
         $r++; */
        /* END MODULEBUILDER IMPORT myobject_list */
    }

    /**
     *  Function called when module is enabled.
     *  The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
     *  It also creates data directories.
     *
     *  @param      string  $options    Options when enabling module ('', 'noboxes')
     *
     *  @return     int             	1 if OK, 0 if KO
     */
    public function init($options = '')
    {
        global $conf, $langs;

        $result = $this->_load_tables('/reports/sql/');
        if ($result < 0) {
            return -1; // Do not activate module if error 'not allowed' returned when loading module SQL queries (the _load_table run sql with run_sql with the error allowed parameter set to 'default')
        }

        // Create extrafields during init
        //include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
        //$extrafields = new ExtraFields($this->db);
        //$result1=$extrafields->addExtraField('reports_myattr1', "New Attr 1 label", 'boolean', 1,  3, 'thirdparty',   0, 0, '', '', 1, '', 0, 0, '', '', 'reports@reports', '$conf->reports->enabled');
        //$result2=$extrafields->addExtraField('reports_myattr2', "New Attr 2 label", 'varchar', 1, 10, 'project',      0, 0, '', '', 1, '', 0, 0, '', '', 'reports@reports', '$conf->reports->enabled');
        //$result3=$extrafields->addExtraField('reports_myattr3', "New Attr 3 label", 'varchar', 1, 10, 'bank_account', 0, 0, '', '', 1, '', 0, 0, '', '', 'reports@reports', '$conf->reports->enabled');
        //$result4=$extrafields->addExtraField('reports_myattr4', "New Attr 4 label", 'select',  1,  3, 'thirdparty',   0, 1, '', array('options'=>array('code1'=>'Val1','code2'=>'Val2','code3'=>'Val3')), 1,'', 0, 0, '', '', 'reports@reports', '$conf->reports->enabled');
        //$result5=$extrafields->addExtraField('reports_myattr5', "New Attr 5 label", 'text',    1, 10, 'user',         0, 0, '', '', 1, '', 0, 0, '', '', 'reports@reports', '$conf->reports->enabled');

        // Permissions
        $this->remove($options);

        $sql = [];

        // Document templates
        $moduledir = 'reports';
        $myTmpObjects = [];
        $myTmpObjects['Myobject_list'] = ['includerefgeneration' => 0, 'includedocgeneration' => 0];

        foreach ($myTmpObjects as $myTmpObjectKey => $myTmpObjectArray) {
            if ($myTmpObjectKey == 'Myobject_list') {
                continue;
            }
            if ($myTmpObjectArray['includerefgeneration']) {
                $src = DOL_DOCUMENT_ROOT.'/install/doctemplates/reports/template_myobject_lists.odt';
                $dirodt = DOL_DATA_ROOT.'/doctemplates/reports';
                $dest = $dirodt.'/template_myobject_lists.odt';

                if (file_exists($src) && !file_exists($dest)) {
                    require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
                    dol_mkdir($dirodt);
                    $result = dol_copy($src, $dest, 0, 0);
                    if ($result < 0) {
                        $langs->load('errors');
                        $this->error = $langs->trans('ErrorFailToCopyFile', $src, $dest);

                        return 0;
                    }
                }

                $sql = array_merge($sql, [
                    'DELETE FROM '.MAIN_DB_PREFIX."document_model WHERE nom = 'standard_".strtolower($myTmpObjectKey)."' AND type = '".strtolower($myTmpObjectKey)."' AND entity = ".$conf->entity,
                    'INSERT INTO '.MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('standard_".strtolower($myTmpObjectKey)."','".strtolower($myTmpObjectKey)."',".$conf->entity.')',
                    'DELETE FROM '.MAIN_DB_PREFIX."document_model WHERE nom = 'generic_".strtolower($myTmpObjectKey)."_odt' AND type = '".strtolower($myTmpObjectKey)."' AND entity = ".$conf->entity,
                    'INSERT INTO '.MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('generic_".strtolower($myTmpObjectKey)."_odt', '".strtolower($myTmpObjectKey)."', ".$conf->entity.')',
                ]);
            }
        }

        return $this->_init($sql, $options);
    }

    /**
     *  Function called when module is disabled.
     *  Remove from database constants, boxes and permissions from Dolibarr database.
     *  Data directories are not deleted.
     *
     *  @param      string	$options    Options when enabling module ('', 'noboxes')
     *
     *  @return     int                 1 if OK, 0 if KO
     */
    public function remove($options = '')
    {
        $sql = [];

        return $this->_remove($sql, $options);
    }
}
