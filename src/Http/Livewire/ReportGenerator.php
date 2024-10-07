<?php

namespace Nhd\Foundation\Http\Livewire;

use Nhd\Foundation\Models\Report;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use Nhd\Foundation\Support\Value;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ReportGenerator extends Component
{
    use AuthorizesRequests, WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $model;
    public $report;
    public $policy;
    private $result;

    public $data;
    public $columns = [];

    public $selectedReportId;

    public function render()
    {
        $this->authorize('reports', $this->model);

        $reports = Report::where('class', $this->report)
            ->orderBy('title', 'ASC')
            ->get();

        $entities = ($this->model)::livewireFilter($this->sections(), $this->data)->paginate(30);

        return view('dashboard::livewire.report-generator', [
            'reports' => $reports,
            'entities' => $entities,
        ]);
    }

    public function sections() {
        return $this->report::sections();
    }

    public function result() {
        if(!$this->result) {
            $this->result = $this->report::result();
        }

        return $this->result;
    }

    public function updatedSelectedReportId() {
        $report = Report::findOrFail($this->selectedReportId);

        $this->columns = (array)($report->data->columns ?? []);
        $this->data = (array)($report->data->data ?? []);
    }

    public function apply() {
        $this->authorize('reports', $this->model);
    }

    public function download() {
        $this->authorize('reports', $this->model);

        $sections = collect($this->sections());
        $modelColumns = $sections->pluck('filters')->merge($sections->pluck('columns'))
            ->filter(fn ($columns) => !is_null($columns))
            ->flatMap(fn ($columns) => $columns)
            ->toArray();

        // if(count(array_diff(array_values($this->columns), array_column($modelColumns, 'name'))) > 0) {
        //     return abort(403);
        // }

        ///////////////////////////////////////////

        $records =($this->model)::livewireFilter($this->sections(), $this->data)
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()
            ->setRightToLeft(true);

        //ثبت نام ستون ها
        $count = 1;
        foreach($this->columns as $column) {
            $sheet->setCellValueByColumnAndRow($count++, 1, $modelColumns[$column]['label'] ?? $column);
        }

        //مقدار دهی
        foreach($records as $key => $record) {
            $count = 1;//شمارنده ستون
            $row = $key + 2;
            foreach($this->columns as $column) {
                $traverse = $modelColumns[$column]['traverse'] ?? null;
                $value = Value::traverse($record, str_replace('__', '.', $column), null, $traverse, ($modelColumns[$column]['items'] ?? null) ? true : false);

                if ($modelColumns[$column]['items'] ?? null) {
                    $property = \Str::afterLast($column, '__');

                    if(!empty($value)) {
                        if($value instanceof Collection) {
                            $items = [];
                            foreach($value as $item) {
                                $items[] = $modelColumns[$column]['items'][$item->$property] ?? null;
                            }

                            $sheet->setCellValueByColumnAndRow($count, $row, implode(', ', $items));
                        }   
                        elseif(is_array($value)) {
                            $items = [];
                            foreach($value as $item) {
                                $items[] = $modelColumns[$column]['items'][$item] ?? null;
                            }

                            $sheet->setCellValueByColumnAndRow($count, $row, implode(', ', $items));
                        }   
                        else {
                            $sheet->setCellValueByColumnAndRow($count, $row, $modelColumns[$column]['items'][$value] ?? null);
                        }
                    }
                }
                else {
                    $sheet->setCellValueByColumnAndRow($count, $row, $value ?? null);
                }

                $sheet->getStyle($count , $row)->getAlignment()->setReadOrder(\PhpOffice\PhpSpreadsheet\Style\Alignment::READORDER_RTL);

                ++$count;
            }
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'export.xlsx');
    }

    public function save($title) {
        $this->authorize('reportsCreate', $this->model);

        if(!empty($title)) {
            $report = Report::create([
                'owner_id' => auth()->user()->id,
                'title' => mb_substr($title, 0, 255),
                'data' => [
                    'columns' => $this->columns,
                    'data' => $this->data,
                ],
                'class' => $this->report,
            ]);

            $this->dispatchBrowserEvent('report-added');
        }
    }

    public function delete() {
        $report = Report::findOrFail($this->selectedReportId);

        $this->authorize('reportsDelete', [$this->model, $report]);

        $report->delete();

        $this->selectedReportId = null;

        $this->dispatchBrowserEvent('report-deleted');
    }

    public function addToColumns($columns = []) {
        $this->columns = array_merge($this->columns, $columns);
    }

    public function removeFromColumns($columns = []) {
        $this->columns = array_filter($this->columns, function($column) use ($columns) {
            return !in_array($column, $columns);
        });
    }
}
