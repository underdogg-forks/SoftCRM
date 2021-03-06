<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\ProductsModel;
use App\Models\SalesModel;
use App\Services\SalesService;
use App\Services\SystemLogService;
use App\Traits\Language;
use Validator;
use Illuminate\Support\Facades\Input;
use View;
use Request;
Use Illuminate\Support\Facades\Redirect;
use Config;

class SalesController extends Controller
{
    use Language;
    
    private $systemLogs;
    private $language;
    private $salesModel;
    private $salesService;

    public function __construct()
    {
        $this->systemLogs = new SystemLogService();
        $this->salesModel = new SalesModel();
        $this->salesService = new SalesService();
    }

    /**
     * @return array
     */
    private function getDataAndPagination()
    {
        $dataWithSaless = [
            'sales' => $this->salesService->getSales(),
            'salesPaginate' => $this->salesService->getPaginate()
        ];

        return $dataWithSaless;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return View::make('crm.sales.index')->with($this->getDataAndPagination());
    }
    
    public function create()
    {
        $dataOfProducts = ProductsModel::pluck('name', 'id');

        return View::make('crm.sales.create')->with(
            [
                'dataOfProducts' => $dataOfProducts,
                'inputText' => $this->getMessage('messages.InputText')
            ]);
    }

    public function store()
    {
        $allInputs = Input::all();

        $validator = Validator::make($allInputs, $this->salesModel->getRules('STORE'));

        if ($validator->fails()) {
            return Redirect::to('sales/create')->with('message_danger', $validator->errors());
        } else {
            if ($sale = $this->salesService->execute($allInputs)) {
                $this->systemLogs->insertSystemLogs('SalesModel has been add with id: ' . $sale, 200);
                return Redirect::to('sales')->with('message_success', $this->getMessage('messages.SuccessSalesStore'));
            } else {
                return Redirect::back()->with('message_success', $this->getMessage('messages.ErrorSalesStore'));
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        return View::make('crm.sales.show')
            ->with([
                'sales' => $this->salesService->getSale($id),
            ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        $dataWithPluckOfProducts = ProductsModel::pluck('name', 'id');

        return View::make('crm.sales.edit')
            ->with([
                'sales' => $this->salesService->getSale($id),
                'dataWithPluckOfProducts' => $dataWithPluckOfProducts
            ]);
    }

    public function update($id)
    {
        $allInputs = Input::all();

        $validator = Validator::make($allInputs, $this->salesModel->getRules('STORE'));

        if ($validator->fails()) {
            return Redirect::back()->with('message_danger', $validator);
        } else {
            if ($this->salesService->update($id, $allInputs)) {
                return Redirect::to('sales')->with('message_success', $this->getMessage('messages.SuccessSalesStore'));
            } else {
                return Redirect::back()->with('message_danger', $this->getMessage('messages.ErrorSalesStore'));
            }
        }
    }

    public function destroy($id)
    {
        $salesDetails = $this->salesService->getSale($id);
        $salesDetails->delete();

        $this->systemLogs->insertSystemLogs('SalesModel has been deleted with id: ' . $salesDetails->id, 200);

        return Redirect::to('sales')->with('message_success', $this->getMessage('messages.SuccessSalesDelete'));
    }

    public function isActiveFunction($id, $value)
    {
        if ($this->salesService->loadIsActiveFunction($id, $value)) {
            $this->systemLogs->insertSystemLogs('SalesModel has been enabled with id: ' . $id, 200);
            return Redirect::back()->with('message_success', $this->getMessage('messages.SuccessSalesActive'));
        } else {
            return Redirect::back()->with('message_danger', $this->getMessage('messages.SalesIsActived'));
        }
    }

    public function search()
    {
        $getValueInput = Request::input('search');
        $findSalesByValue = $this->salesService->loadSearch($getValueInput);
        $dataOfSales = $this->getDataAndPagination();

        if (!$findSalesByValue > 0) {
            return redirect('sales')->with('message_danger', $this->getMessage('messages.ThereIsNoSales'));
        } else {
            $dataOfSales += ['sales_search' => $findSalesByValue];
            Redirect::to('sales/search')->with('message_success', 'Find ' . $findSalesByValue . ' sales!');
        }

        return View::make('crm.sales.index')->with($dataOfSales);
    }
}
