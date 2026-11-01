<?php

namespace App\Support;

class PageAccess
{
    /**
     * The permissions that open each part of the admin, shown as the grid on the
     * Roles page. routes/web.php and the sidebar check these same names.
     */
    public const SECTIONS = [
        'access posts' => 'Posts',
        'access pages' => 'Pages',
        'access media' => 'Media',
        'access categories' => 'Categories',
        'access tags' => 'Tags',
        'access comments' => 'Comments',
        'access gold silver' => 'Gold & Silver',
        'access patro' => 'Patro Events',
        'access rashifal' => 'Rashifal',
        'access genealogy' => 'Genealogy',
        'access advertisements' => 'Advertisements',
        'access analytics' => 'Analytics',
        'view visitor details' => 'Visitor IPs and log',
        'access seo' => 'SEO',
        'access seo settings' => 'SEO Settings',
        'access users' => 'Users',
        'access roles' => 'Roles',
    ];
}
