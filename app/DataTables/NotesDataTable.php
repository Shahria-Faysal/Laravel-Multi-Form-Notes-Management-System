<?php

namespace App\DataTables;

use App\Models\Note;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Carbon;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class NotesDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<note> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
         $dataTable = (new EloquentDataTable($query))
            ->editColumn('created_at', function ($notes) {
                return Carbon::parse($notes->created_at)->format('d-m-Y');
            })
            ->editColumn('updated_at', function ($notes) {
                return Carbon::parse($notes->updated_at)->format('d-m-Y');
            })
            ->rawColumns(['action'])
            ->setRowId('id');

        if ($this->tableId === 'trash-table') {
            $dataTable->addColumn('action', function ($notes) {
                return '
                <div class="gap-2 d-flex">
                    <button class="btn btn-success btn-sm restore-user" data-id="' . $notes->id . '">Restore</button>
                    <button class="btn btn-danger btn-sm force-delete" data-id="' . $notes->id . '">Delete Forever</button>
                </div>
            ';
            })
             ->editColumn('deleted_at', function ($notes) {
                return Carbon::parse($notes->deleted_at)->format('d-m-Y');
            });
        } else {
            $dataTable->addColumn('action', function ($notes) {
                return '
                <button class="btn btn-primary btn-sm edit-user" data-id="' . $notes->id . '">Edit</button>
                <button class="btn btn-danger btn-sm delete-user" data-id="' . $notes->id . '">Delete</button>
            ';
            });
        }

        return $dataTable;
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<note>
     */
    public function query(Note $model): QueryBuilder
    {
        $type = $this->tableId;
        $query = $model->newQuery();

        if ($type === 'trash-table') {
            $query->onlyTrashed();
        }

        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $tableId = $this->tableId ?? 'notes-table';
        $ajaxUrl = route('notes.table.data');
        // $ajaxUrl = '/table-data';
        return $this->builder()
                    ->setTableId($tableId)
                    ->columns($this->getColumns())
                    ->ajax([
                        'url'  => $ajaxUrl, 
                        'type' => 'GET',
                        'data' => "function(d) { 
                            d.tableId = '{$tableId}';
                        }",
                    ])
                    ->parameters([
                        'order' => [[0, 'asc']],
                        // 'dom' => '<"top mb-2"Bf>lrt<"bottom d-flex    justify-content-between mt-3"ip>',
                        'initComplete' => 'function() {
            
                            const table = this.api();

                            const $thead = $(table.table().header());

                            const $filterRow = $thead.find("tr").clone().addClass("filter");

                            $filterRow.find("th").each(function() {
                                
                                const $currentTh = $(this);

                                if(!$currentTh.hasClass("no-search")){
                                    const input = $(`<input type="text" class="form-control form-control-sm" placeholder="Search ${$currentTh.text()}" />`);

                                    $currentTh.html(input);

                                    $(input).on("click", function(event) {
                                        event.stopPropagation();
                                    });

                                    $(input).on("keyup change clear",function() {
                                        if(table.column($currentTh.index()).search() !== this.value) {
                                            table.column($currentTh.index()).search(this.value).draw();
                                        }
                                    });
                                } else{
                                    $currentTh.empty();    
                                }
                           
                            });

                            $thead.append($filterRow);
                                                      

                        }'
                    ])
                    ->orderBy(1)
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('excel'),
                        Button::make('csv'),
                        Button::make('pdf'),
                        Button::make('print'),
                        Button::make('reset'),
                        Button::make('reload')
                                ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
         $columns = [
            Column::make('id')->width(120),
            Column::make('title'),
            Column::make('note'),
            Column::make('created_at'),
            Column::make('updated_at'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(100)
                ->addClass('text-center no-search'),
        ];

        if ($this->tableId === 'trash-table') {
            $columns[] = Column::make('deleted_at');
        }

        return $columns;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Notes_' . date('YmdHis');
    }
}
