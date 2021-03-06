<?php

namespace App\Services;

use App\Models\DealsModel;

class DealsService
{
    private $dealsModel;

    public function __construct()
    {
        $this->dealsModel = new DealsModel();
    }

    public function getDeals()
    {
        return DealsModel::all()->sortByDesc('created_at');
    }

    public function getPaginate()
    {
        return DealsModel::paginate(Config::get('crm_settings.pagination_size'));
    }

    public function execute($allInputs)
    {
        return $this->dealsModel->insertRow($allInputs);
    }

    public function getDeal(int $id)
    {
        return DealsModel::find($id);
    }

    public function update(int $id, $allInputs)
    {
        return $this->dealsModel->updateRow($id, $allInputs);
    }

    public function loadSearch($getValueInput)
    {
        return count($this->dealsModel->trySearchDealsByValue('name', $getValueInput, 10));
    }
}