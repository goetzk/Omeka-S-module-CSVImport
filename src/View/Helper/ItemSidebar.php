<?php
namespace CSVImport\View\Helper;

use Zend\View\Helper\AbstractHelper;

class ItemSidebar extends AbstractHelper
{
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function __invoke()
    {
        $userRole = $this->user->getRole();
        $html = "
        <div id='item-data' class='sidebar flags'>
            <a href='#' class='sidebar-close o-icon-close'><span class='screen-reader-text'>Close Me</span></a>
            <h3>Item Data: </h3>

            <p>Data from this column applies to the entire item being imported.</p>
            <p>Settings here will override any corresponding settings in Basic Import Settings.</p>
            <ul>
                <li data-flag='column-itemset-id' data-flag-type='item-data'>
                    <a href='#' class='button'><span>Item Set ID</span></a>
                </li>
                <li data-flag='column-resourcetemplate' data-flag-type='item-data'>
                    <a href='#' class='button'><span>Resource Template Name</span></a>
                </li>
                <li data-flag='column-resourceclass' data-flag-type='item-data'>
                    <a href='#' class='button'><span>Resource Class Term</span></a>
                </li>
                ";
        if( ($userRole == 'global_admin') || ($userRole == 'site_admin')) {

            $html .="<li data-flag='column-owneremail'>
                    <a href='#' class='button'><span>Owner Email Address</span></a>
                </li>";
        }
        $html .= "</ul></div>";
        return $html;
    }
}