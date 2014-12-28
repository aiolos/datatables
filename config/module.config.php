<?php
return array(
    'view_manager' => array(
        'template_path_stack' => array(
            'Datatables' => __DIR__ . '/../view',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'Datatable' => 'Datatables\View\Helper\Datatable',
        ),
    ),
    'assetic_configuration' => array(
        'debug' => false,
        'buildOnRequest' => false,

        // this is specific to this project
        'webPath' => realpath('public/assets'),
        'basePath' => 'assets',

        'default' => array(
            'assets' => array(
                '@base_js',
                '@datatables_css',
                '@datatables_js',
            ),
            'options' => array(
                'mixin' => true
            ),
        ),

        'modules' => array(
            'Datatables' => array(
                'root_path' => __DIR__ . '/../assets',
                'collections' => array(
                    'datatables_css' => array(
                        'assets' => array(
                            'css/jquery.dataTables.css',
                            'css/dataTables.tableTools.min.css',
                            'css/dataTables.responsive.css',
                        ),
                        'filters' => array(
                            'CssRewriteFilter' => array(
                                'name' => 'Assetic\Filter\CssRewriteFilter'
                            )
                        ),
                        'options' => array(
                            'output' => 'css/datatables.css'
                        )
                    ),

                    'base_js' => array(
                        'assets' => array(
                            'js/jquery-1.11.2.min.js',
                        ),
                        //'filters' => array(
                        //    '?JSMinFilter' => array(
                        //        'name' => 'Assetic\Filter\JSMinFilter'
                        //    ),
                        //),
                    ),

                    'datatables_js' => array(
                        'assets' => array(
                            'js/jquery.dataTables.min.js',
                            //'js/jquery.dataTables.extensions.js',
                            //'js/datatables-bootstrap.js',
                            'js/dataTables.tableTools.min.js',
                            'js/dataTables.responsive.min.js',
                        ),
                        //'filters' => array(
                        //    '?JSMinFilter' => array(
                        //        'name' => 'Assetic\Filter\JSMinFilter'
                        //    ),
                        //),
                    ),

                    'datatables_images' => array(
                        'assets' => array(
                            'images/*.png',
                            'images/*.ico',
                        ),
                        'options' => array(
                            'move_raw' => true,
                        )
                    ),
                    'datatables_swf_pdf' => array(
                        'assets' => array(
                            'swf/copy_csv_xls_pdf.swf',
                        ),
                        'options' => array(
                            'output' => 'swf/copy_csv_xls_pdf.swf',
                        )
                    ),
                    'datatables_swf' => array(
                        'assets' => array(
                            'swf/copy_csv_xls.swf',
                        ),
                        'options' => array(
                            'output' => 'swf/copy_csv_xls.swf',
                        )
                    ),
                ),
            ),
        ),
    ),
);
