<?php

namespace App\Interfaces\Repo\Backend;

use App\Interfaces\EloquentRepositoryInterface;

interface QuotationRepo extends EloquentRepositoryInterface
{
    public function checkRequestValidity($request);

    public function generateQuotationNo(): string;

    public function createWithItems(array $quotationData, array $itemsData);

    public function findWithItemsById($id);

    public function getQuotationList($orgId = null, $branchId = null);

    public function approveQuotation($quotationId, $approvalLevel, $status, $comments = null, $approverInfo = []);

    public function getApprovalStatus($quotationId);
}
