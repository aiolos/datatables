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

    /* Special live events */
    protected $rowClickCallback;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setDisplayLength(25);
        $this->setOption('bLengthChange', true);
        $this->setOption('bFilter', false);
        $this->setOption('bJQueryUI', false);
        $this->setOption('bAutoWidth', false);
        $this->setOption('sPaginationType', 'bs_full');
        $this->setOption('sDom', '<"well"<"row"t>><"row"<"col-xs-6"i><"col-xs-6"p>>'); //
        //$this->setOption("sDom", "<'row-fluid'<'span6'T><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>");
        $this->addPostVariable('version', '2.0');
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

    public function addCheckboxColumn($name, $label = null, $options = array(), $inverse = false)
    {
        if (is_null($label)) {
            $label = '<span class="checkboxSelectAll ui-icon ui-icon-check option-icon"></span>';
        }
        $options = array_merge(
            array(
                'mData' => new Expr('function(data) { return "<span class=\"checkBoxRow ui-icon ui-icon-checkbox option-icon ' . ($inverse ? 'ui-icon-checkbox-checked' : '') . '\"></span>" }'),
                'bSortable' => false
            ),
            $options
        );
        $this->addColumn($name, $label, $options);
        $this->onRowClick(
            'function(id, node, event) {
                if ($(event.target).hasClass("checkBoxRow")) {
                    $(event.target).toggleClass("ui-icon-checkbox-checked");

                    var condition = ' . ($inverse ? '!' : '') . '$(event.target).hasClass("ui-icon-checkbox-checked");

                    if (condition) {
                        Regiecentrale.Datatables.addToMultiSelection("' . $this->getId() . '", id);
                    } else {
                        Regiecentrale.Datatables.removeFromMultiSelection("' . $this->getId() . '", id);
                    }
                }
            }'
        );
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
        $this->setOption('sDom', $template);
        return $this;
    }

    public function setDisplayLength($length)
    {
        $this->setOption('iDisplayLength', $length);
        /* @todo change this ugly ass fix */
        if ($length == 10) {
            //$this->setOption("sDom", "<'row-fluid'<'span6'T><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>");
            $this->setOption('sDom', '<"datatablebox datatablebox-small datatable"<"filterDescription">Tft><"bottom"p<"TableButtons">i><"clear">');
        } else {
            //$this->setOption("sDom", "<'row-fluid'<'span6'T><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>");
            $this->setOption('sDom', '<"datatablebox datatable"<"pull-right"<"filterDescription">T>ft><"bottom"p<"TableButtons">i><"clear">');
        }

        return $this;
    }

    public function setFilterDescription($description)
    {
        $this->filterDescription = $description;
        return $this;
    }

    public function setDataUrl($url)
    {
        $this->setOption('bServerSide', true);
        $this->setOption('sAjaxSource', $url);
        return $this;
    }

    public function setDataProperty($name)
    {
        $this->setOption('sAjaxDataProp', $name);
        return $this;
    }

    public function setSorting($columnName, $direction)
    {
        $columnIndex = $this->getColumnIndexByName($columnName);
        $this->setOption('aaSorting', array(array($columnIndex, $direction)));

        return $this;
    }

    public function addPostVariable($key, $value)
    {
        $this->postVariables[$key] = $value;
        return $this;
    }

    public function onRowClick($callback)
    {
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

    public function onRowClickView($url, $element)
    {
        $this->onRowClick(
            'function(id, node) {
                ' . $this->getId() . 'DataTable.fnChangeDisplayLength(10, node);
                $.ajax({
                    url: "' . $url . '/format/html/id/" + id,
                }).done(function(data) {
                    $("#' . $element . '").slideDown(400, function() { $("#' . $element . '").html(data) });
                });

                $(document).on(
                    "regiecentrale:closeDetailView",
                    function() {
                        ' . $this->getId() . 'DataTable.fnChangeDisplayLength(25);
                        $("#' . $element . '").html("").slideUp(400);
                        Regiecentrale.Datatables.setCurrentRow(' . $this->getId() . ', null);
                        $("table#' . $this->getId() . '").find("tbody tr.selectedrow").removeClass("selectedrow");
                    }
                );
            }'
        );
        return $this;
    }

    public function onRowClickHighlight()
    {
        $this->onRowClick(
            'function(id, node) {
                $("table#' . $this->getId() . '").find("tbody tr.selectedrow").removeClass("selectedrow");
                node.addClass("selectedrow");
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
                    $(".TableButtons").html("<div class=\'dataTables_buttons pull-right\' id=\''. $this->getId() . 'Buttons\'>' . implode(' ', $this->buttons) . '</div>");
                };
            </script>
        ';
        //return '<div class="dataTables_buttons pull-right" id="'. $this->getId() . 'Buttons">' . implode(' ', $this->buttons) . '</div>';
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
        $table = '<table id="' . $this->getId() . '" class="table table-striped table-condensed table-hover"><thead><tr>';
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
        $callback = 'function (aoData) { aoData.push(';
        foreach ($this->postVariables as $key => $value) {
            $callback .= \Zend\Json\Json::encode(array('name' => $key, 'value' => $value), false, array('enableJsonExprFinder' => true)) . ',';
        }
        if (count($this->postVariables) > 0) {
            $callback = substr($callback, 0, -1);
        }
        $callback .=  ');}';

        $this->setOption(
            'oTableTools',
            array(
                "aButtons" => $this->headerButtons
            )
        );

        /* Merge all options to a single array */
        $initOptions = array_merge(
            $this->options,
            array(
                'aoColumns' => $columns,
                'oLanguage' => $this->getTranslations(),
                'fnServerParams' => new \Zend\Json\Expr($callback),
                'fnCreatedRow' => new \Zend\Json\Expr(
                    'function(nRow, aData, iDataIndex) {
                        if ($(nRow).attr("data-id") == null && aData.id != null) {
                            $(nRow).attr("data-id", aData.id);
                            if (parseInt(aData.id) === parseInt(Regiecentrale.Datatables.getCurrentRow(' . $this->getId() . '))) {
                                $(nRow).addClass("selectedrow");
                            }
                        }
                    }'
                ),
                'fnDrawCallback' => new \Zend\Json\Expr(
                    'function() {
                        addButtons();
                        setFilterDescription();
                        $("table#' . $this->getId() . ' tbody tr").on("click", function(e) {
                            if ($(e.target).hasClass("btn")) {
                                return;
                            }
                            var node = $(this);

                            if (node.attr("data-id") === undefined) {
                                return;
                            }

                            Regiecentrale.Datatables.setCurrentRow(' . $this->getId() . ', $(this).attr("data-id"));
                            var callbacks = ' . \Zend\Json\Json::encode($this->rowClickCallback, false, array('enableJsonExprFinder' => true)) . ';
                            var result = null;
                            if (callbacks !== null && callbacks.length > 0) {
                                $.each(callbacks, function(index, callback) {
                                    callback(Regiecentrale.Datatables.getCurrentRow(' . $this->getId() . '), node, e);

                                     return !e.isPropagationStopped();

                                });
                            }
                        });
                    }'
                )
            )
        );

        if (!is_null($this->data)) {
            $initOptions['aaData'] = $this->data;
        }

        /* The actual datatable */
        return '
            <script type="text/javascript">
                $(function() {
                    ' . $this->getId() . 'DataTable = $("table#' . $this->getId() . '").dataTable(' . \Zend\Json\Json::encode($initOptions, false, array('enableJsonExprFinder' => true)) . ');
                });

            </script>
        ';
    }

    protected function renderColumn($column)
    {
        $options = $column->options;
        /* Name */
        $options['sName'] = $column->name;

        /* mData fallback */
        if (!array_key_exists('mData', $options)) {
            $options['mData'] = $column->name;
        }

        return $options;
    }

    protected function getTranslations()
    {
        return array(
            'sLengthMenu' => '%1$s per pagina', '_MENU_',
            'sZeroRecords' => 'Geen resultaten',
            'sInfo' => '_START_ - _END_ van _TOTAL_',
            'sInfoEmpty' => 'Geen resultaten',
            'sInfoFiltered' => '(totaal _MAX_)',
            'oPaginate' => array(
                'sFirst' =>  "",
                'sPrevious' => "",
                'sNext' => "",
                'sLast' => ""
            )
        );
    }

    public function __toString()
    {
        return $this->render();
    }
}
