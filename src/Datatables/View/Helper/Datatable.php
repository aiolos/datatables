<?php
/**
 * Datatables
 *
 * PHP Version 5.3
 *
 * @category  Helper
 * @package   Datatables\View\Helper
 * @author    Henri de Jong <henridejong@gmail.com>
 * @link      http://github.com/aiolos/datatables
 */
namespace Datatables\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Json\Expr;

/**
 * Datatable
 *
 * @category Datatables
 * @package  Datatables\View\Helper
 * @author   Henri de Jong <henridejong@gmail.com>
 * @link     http://github.com/aiolos/datatables
 */
class Datatable extends AbstractHelper
{
    protected $id;
    protected $data;
    protected $options = array();
    protected $columns = array();
    protected $postVariables = array();
    protected $buttons = array();
    protected $headerButtons = array();
    protected $filterDescription;
    protected $classes = array(
        'table',
        'table-striped',
        'table-condensed',
        'table-hover',
    );

    /* Special live events */
    protected $rowClickCallback;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setDisplayLength(25);
        $this->setOption('lengthChange', true);
        $this->setOption('responsive', true);
        $this->setOption('searching', false);
        $this->setOption('autoWidth', true);
        $this->setOption('pagingType', 'full_numbers');
        $this->setOption('dom', '<"datatablebox datatable"<"pull-right"<"filterDescription"><"headerTableButtons col-xs-6">T><"pull-left"f>t><"bottom"p<"TableButtons">i><"clear">');
    }

    public function __invoke()
    {
        return new self();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function addColumn($name, $label = null, $options = array())
    {
        $column = new \stdClass();
        $column->name = $name;
        $column->label = is_null($label) ? $name : $label;
        $column->options = $options;
        $this->columns[] = $column;

        return $this;
    }

    public function getColumnIndexByName($columnName)
    {
        foreach ($this->columns as $index => $column) {
            if ($column->name == $columnName) {
                return $index;
            }
        }

    }

    public function addButton($button)
    {
        $this->buttons[] = $button;

        return $this;
    }

    public function addHeaderButton($button)
    {
        $this->headerButtons[] = $button;

        return $this;
    }

    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    public function getOption($key)
    {
        if (array_key_exists($key, $this->options)) {
            return $this->options[$key];
        }
        return;
    }

    public function hasOption($key)
    {
        return array_key_exists($key, $this->options);
    }

    public function setDomTemplate($template)
    {
        $this->setOption('dom', $template);
        return $this;
    }

    public function setDisplayLength($length)
    {
        $this->setOption('pageLength', $length);

        return $this;
    }

    public function setFilterDescription($description)
    {
        $this->filterDescription = $description;
        return $this;
    }

    public function setAjaxData($properties)
    {
        $this->setOption('serverSide', true);
        $this->setOption('ajax', $properties);

        return $this;
    }

    public function setSorting($columnName, $direction)
    {
        $columnIndex = $this->getColumnIndexByName($columnName);
        $this->setOption('order', array(array($columnIndex, $direction)));

        return $this;
    }

    public function addPostVariable($key, $value)
    {
        $this->postVariables[$key] = $value;
        return $this;
    }

    public function onRowClick($callback)
    {
        if (!in_array('rowClick', $this->classes)) {
            $this->classes[] = 'rowClick';
        }
        $this->rowClickCallback[] = new \Zend\Json\Expr($callback);
        return $this;
    }

    public function onRowClickRedirect($url)
    {
        $this->onRowClick(
            'function(id, node) {
                $(window).attr("location", "' . $url . '/id/" + id);
            }'
        );
        return $this;
    }

    public function render()
    {
        return
            $this->renderTable() .
            $this->renderTableJavascript() .
            $this->renderTableButtons() .
            $this->renderTableHeader();
    }

    public function renderTableButtons()
    {
        /* The actual datatable */
        return '
            <script type="text/javascript">
                function addButtons() {
                    $(".TableButtons").html("<div class=\'dataTables_buttons pull-right\' id=\''. $this->getId() . 'Buttons\'>'
                    . '<div style=\'margin: 20px 0;\'>'
                    . implode(' ', $this->buttons)
                    . '</div>'
                    . '</div>");
                    $(".headerTableButtons").html("<div class=\'dataTables_buttons pull-right\' id=\''. $this->getId() . 'HeaderButtons\'>'
                    . implode(' ', $this->headerButtons)
                    . '</div>");
                };
            </script>
        ';
    }

    public function renderTableHeader()
    {
        /* The actual datatable */
        return '
            <script type="text/javascript">
                function setFilterDescription() {
                    $(".filterDescription").text("'. $this->filterDescription . '");
                };
            </script>
        ';
    }

    public function renderTable()
    {
        $table = '<table id="' . $this->getId() . '" class="' . join(' ', $this->classes) . '"><thead><tr>';
        foreach ($this->columns as $column) {
            $table .= '<th>' . $column->label . '</th>';
        }
        $table .= '</tr></thead><tbody></tbody></table>';

        return $table;
    }

    public function renderTableJavascript()
    {
        /* Render the columns */
        $columns = array();
        foreach ($this->columns as $column) {
            $columns[] = $this->renderColumn($column);
        }

        /* Render a specific callback to bridge the "gap" between the helpers public interface and the datatable public interface */
        //        $callback = 'function (aoData) { aoData.push(';
        //        foreach ($this->postVariables as $key => $value) {
        //            $callback .= \Zend\Json\Json::encode(
        //                array('name' => $key, 'value' => $value),
        //                false,
        //                array('enableJsonExprFinder' => true)
        //            ) . ',';
        //        }
        //        if (count($this->postVariables) > 0) {
        //            $callback = substr($callback, 0, -1);
        //        }
        //        $callback .=  ');}';

        /* Merge all options to a single array */
        $initOptions = array_merge(
            $this->options,
            array(
                'columns' => $columns,
                'language' => $this->getTranslations(),
                //'fnServerParams' => new \Zend\Json\Expr($callback),
                'drawCallback' => new \Zend\Json\Expr(
                    'function() {
                        addButtons();
                        var table = $("table#' . $this->getId() . '").DataTable();
                        $("table#' . $this->getId() . ' tbody tr").on("click", function(e) {
                            if ($(e.target).hasClass("btn")
                                || $(e.target).is("select")
                                || $(e.target).is("option")
                                || $(e.target).is("input")
                                || $(e.target).is("a")
                                || $(e.target).is("button")
                            ) {
                                e.stopPropagation();
                                return;
                            }
                            var node = $(this);
                            var rowData = table.row( this ).data();

                            var callbacks = ' . \Zend\Json\Json::encode($this->rowClickCallback, false, array('enableJsonExprFinder' => true)) . ';
                            var result = null;
                            if (callbacks !== null && callbacks.length > 0) {
                                $.each(callbacks, function(index, callback) {
                                    callback(rowData.id, node, e);
                                    return !e.isPropagationStopped();
                                });
                            }
                        });
                    }'
                )
            )
        );

        if (!is_null($this->data)) {
            $initOptions['data'] = $this->data;
        }

        /* The actual datatable */
        return '
            <script type="text/javascript">
                $(function() {
                    ' . $this->getId() . 'DataTable = $("table#' . $this->getId() . '").DataTable('
                    . \Zend\Json\Json::encode($initOptions, false, array('enableJsonExprFinder' => true))
                    . ');
                });
            </script>
        ';
    }

    protected function renderColumn($column)
    {
        $options = $column->options;
        /* Name */
        $options['name'] = $column->name;

        /* data fallback */
        if (!array_key_exists('data', $options)) {
            $options['data'] = $column->name;
        }

        return $options;
    }

    protected function getTranslations()
    {
        return array(
            'lengthMenu' => '%1$s per pagina', '_MENU_',
            'zeroRecords' => 'Geen resultaten',
            'info' => '_START_ - _END_ van _TOTAL_',
            'infoEmpty' => 'Geen resultaten',
            'infoFiltered' => '(totaal _MAX_)',
            'paginate' => array(
                'first' =>  "<i class='glyphicon glyphicon-backward'></i>",
                'previous' => "<i class='glyphicon glyphicon-chevron-left'></i>",
                'next' => "<i class='glyphicon glyphicon-chevron-right'></i>",
                'last' => "<i class='glyphicon glyphicon-forward'></i>"
            ),
            'search' => '',
            'searchPlaceholder' => 'Zoek',
            'thousands' => '.',
            'decimal' => ',',
        );
    }

    public function __toString()
    {
        return $this->render();
    }
}
