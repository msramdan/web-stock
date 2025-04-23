<?php

return [
    /**
     * If any input file(image) as default will use options below.
     */
    "image" => [
        /**
         * Path for store the image.
         *
         * Available options:
         * 1. public
         * 2. storage
         * 3. S3
         */
        "disk" => "storage",

        /**
         * Will be used if image is nullable and default value is null.
         */
        "default" => "https://via.placeholder.com/350?text=No+Image+Avaiable",

        /**
         * Crop the uploaded image using intervention image.
         */
        "crop" => true,

        /**
         * When set to true the uploaded image aspect ratio will still original.
         */
        "aspect_ratio" => true,

        /**
         * Crop image size.
         */
        "width" => 500,
        "height" => 500,
    ],

    "format" => [
        /**
         * Will be used to first year on select, if any column type year.
         */
        "first_year" => 1970,

        /**
         * If any date column type will cast and display used this format, but for input date still will used Y-m-d format.
         *
         * another most common format:
         * - M d Y
         * - d F Y
         * - Y m d
         */
        "date" => "Y-m-d",

        /**
         * If any input type month will cast and display used this format.
         */
        "month" => "Y/m",

        /**
         * If any input type time will cast and display used this format.
         */
        "time" => "H:i",

        /**
         * If any datetime column type or datetime-local on input, will cast and display used this format.
         */
        "datetime" => "Y-m-d H:i:s",

        /**
         * Limit string on index view for any column type text or long text.
         */
        "limit_text" => 100,
    ],

    /**
     * It will be used for generator to manage and showing menus on sidebar views.
     *
     * Example:
     * [
     *   'header' => 'Main',
     *
     *   // All permissions in menus[] and submenus[]
     *   'permissions' => ['test view'],
     *
     *   menus' => [
     *       [
     *          'title' => 'Main Data',
     *          'icon' => '<i class="bi bi-collection-fill"></i>',
     *          'route' => null,
     *
     *          // permission always null when isset submenus
     *          'permission' => null,
     *
     *          // All permissions on submenus[] and will empty[] when submenus equals to []
     *          'permissions' => ['test view'],
     *
     *          'submenus' => [
     *                 [
     *                     'title' => 'Tests',
     *                     'route' => '/tests',
     *                     'permission' => 'test view'
     *                  ]
     *               ],
     *           ],
     *       ],
     *  ],
     *
     * This code below always changes when you use a generator, and maybe you must format the code.
     */
    "sidebars" => [
        [
            'header' => 'Master Data',
            'permissions' => [
                'jenis material view',
                'unit satuan view'
            ],
            'menus' => [
                [
                    'title' => 'Master Data',
                    'icon' => '<i class="bi bi-collection"></i>',
                    'route' => [
                        'jenis-material*',
                        'unit-satuan*'
                    ],
                    'permissions' => [
                        'jenis material view',
                        'unit satuan view'
                    ],
                    'submenus' => [
                        [
                            'title' => 'Jenis Material',
                            'route' => '/jenis-material',
                            'permission' => 'jenis material view',
                            'icon' => '<i class="bi bi-list"></i>'
                        ],
                        [
                            'title' => 'Unit Satuan',
                            'route' => '/unit-satuan',
                            'permission' => 'unit satuan view',
                            'icon' => '<i class="bi bi-list"></i>'
                        ]
                    ]
                ]
            ]
        ],
        [
            'header' => 'Barang',
            'permissions' => [
                'barang view'
            ],
            'menus' => [
                [
                    'title' => 'Daftar Barang',
                    'icon' => '<i class="bi bi-box-seam"></i>',
                    'route' => '/barang',
                    'permission' => 'barang view',
                    'permissions' => [],
                    'submenus' => []
                ]
            ]
        ],
        [
            'header' => 'BoM',
            'permissions' => [
                'bom view'
            ],
            'menus' => [
                [
                    'title' => 'BoM',
                    'icon' => '<i class="bi bi-diagram-3"></i>',
                    'route' => '/bom',
                    'permission' => 'bom view',
                    'permissions' => [],
                    'submenus' => []
                ]
            ]
        ],
        [
            'header' => 'Transaksi',
            'permissions' => [
                'transaksi stock in view',
                'transaksi stock out view',
                'laporan view'
            ],
            'menus' => [
                [
                    'title' => 'Transaksi In Out',
                    'icon' => '<i class="bi bi-arrow-left-right"></i>',
                    'route' => [
                        'transaksi-stock-in*',
                        'transaksi-stock-out*',
                        'laporan*'
                    ],
                    'permissions' => [
                        'transaksi stock in view',
                        'transaksi stock out view',
                        'laporan view'
                    ],
                    'submenus' => [
                        [
                            'title' => 'Stock In',
                            'route' => '/transaksi-stock-in',
                            'permission' => 'transaksi stock in view'
                        ],
                        [
                            'title' => 'Stock Out',
                            'route' => '/transaksi-stock-out',
                            'permission' => 'transaksi stock out view'
                        ],
                        [
                            'title' => 'Laporan',
                            'route' => '/laporan',
                            'permission' => 'laporan view'
                        ]
                    ]
                ]
            ]
        ],
        [
            'header' => 'Company',
            'permissions' => [
                'company view'
            ],
            'menus' => [
                [
                    'title' => 'Daftar Company',
                    'icon' => '<i class="bi bi-list"></i>',
                    'route' => '/company',
                    'permission' => 'company view',
                    'permissions' => [],
                    'submenus' => []
                ]
            ]
        ],
        [
            'header' => 'Utilities',
            'permissions' => [
                'user view',
                'role & permission view',
                'backup database view'
            ],
            'menus' => [
                [
                    'title' => 'Utilities',
                    'icon' => '<i class="bi bi-gear-fill"></i>',
                    'route' => [
                        'users*',
                        'roles*',
                        'backup-database*'
                    ],
                    'permissions' => [
                        'user view',
                        'role & permission view',
                        'backup database view'
                    ],
                    'submenus' => [
                        [
                            'title' => 'User',
                            'route' => '/users',
                            'permission' => 'user view'
                        ],
                        [
                            'title' => 'Roles & permissions',
                            'route' => '/roles',
                            'permission' => 'role & permission view'
                        ],
                        [
                            'title' => 'Backup Database',
                            'route' => '/backup-database',
                            'permission' => 'backup database view',
                            'icon' => '<i class="bi bi-database"></i>'
                        ]
                    ]
                ]
            ]
        ]
    ]
];
