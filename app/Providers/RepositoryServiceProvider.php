<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\EloquentRepositoryInterface;
use App\Interfaces\EloquentBaseRepository;

// Interfaces

use App\Interfaces\Repo\Backend\BackendMenuRepo;
use App\Interfaces\Repo\Backend\UserAccessTypeRepo;
use App\Interfaces\Repo\Backend\BrandRepo;
use App\Interfaces\Repo\Backend\BranchRepo;
use App\Interfaces\Repo\Backend\CategoryRepo;
use App\Interfaces\Repo\Backend\DivisionRepo;
use App\Interfaces\Repo\Backend\DashBoardLogRepo;
use App\Interfaces\Repo\Backend\MenuPermissionRepo;
use App\Interfaces\Repo\Backend\SupplierRepo;
use App\Interfaces\Repo\Backend\StockOutRepo;
use App\Interfaces\Repo\Backend\ItemSwapRepo;

use App\Interfaces\Repo\Backend\ItemsCsvUploadRepo;
use App\Interfaces\Repo\Backend\ItemsRepo;
use App\Interfaces\Repo\Backend\ItemsInventoryRepo;
use App\Interfaces\Repo\Backend\ItemsReceiveRepo;
use App\Interfaces\Repo\Backend\UnitRepo;
use App\Interfaces\Repo\Backend\UserRepo;
use App\Interfaces\Repo\Backend\DeliveryChallanRepo;
use App\Interfaces\Repo\Backend\ProductReturnRepo;
use App\Interfaces\Repo\Backend\RequisitionRepo;
use App\Interfaces\Repo\Backend\QuotationRepo;
use App\Interfaces\Repo\Backend\InventoryReportRepo;
use App\Interfaces\Repo\Backend\InventoryBarcodeRepo;
use App\Interfaces\Repo\Backend\OrganizationRepo;

// Implementations
use App\Repository\Backend\BackendMenuRepoImpl;
use App\Repository\Backend\UserAccessTypeRepoImpl;
use App\Repository\Backend\BrandRepoImpl;
use App\Repository\Backend\BranchRepoImpl;
use App\Repository\Backend\CategoryRepoImpl;
use App\Repository\Backend\DivisionRepoImpl;
use App\Repository\Backend\DashBoardLogRepoImpl;
use App\Repository\Backend\MenuPermissionRepoImpl;
use App\Repository\Backend\SupplierRepoImpl;
use App\Repository\Backend\StockOutRepoImpl;
use App\Repository\Backend\ItemSwapRepoImpl;
use App\Repository\Backend\ItemsRepoImpl;
use App\Repository\Backend\ItemsInventoryRepoImpl;
use App\Repository\Backend\ItemsCsvUploadRepoImpl;
use App\Repository\Backend\ItemsReceiveRepoImpl;
use App\Repository\Backend\UnitRepoImpl;
use App\Repository\Backend\UserRepoImpl;
use App\Repository\Backend\DeliveryChallanRepoImpl;
use App\Repository\Backend\ProductReturnRepoImpl;
use App\Repository\Backend\RequisitionRepoImpl;
use App\Repository\Backend\QuotationRepoImpl;
use App\Repository\Backend\InventoryReportRepoImpl;
use App\Repository\Backend\InventoryBarcodeRepoImpl;
use App\Repository\Backend\OrganizationRepoImpl;



class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(BackendMenuRepo::class, BackendMenuRepoImpl::class);
        $this->app->bind(BrandRepo::class, BrandRepoImpl::class);
        $this->app->bind(BranchRepo::class, BranchRepoImpl::class);
        $this->app->bind(UserAccessTypeRepo::class, UserAccessTypeRepoImpl::class); 
       
      
        
        $this->app->bind(CategoryRepo::class, CategoryRepoImpl::class);
        $this->app->bind(DivisionRepo::class, DivisionRepoImpl::class);
        $this->app->bind(DashBoardLogRepo::class, DashBoardLogRepoImpl::class);
        $this->app->bind(EloquentRepositoryInterface::class, EloquentBaseRepository::class);
        $this->app->bind(MenuPermissionRepo::class, MenuPermissionRepoImpl::class);
        $this->app->bind(StockOutRepo::class, StockOutRepoImpl::class);
        $this->app->bind(ItemSwapRepo::class, ItemSwapRepoImpl::class);
        $this->app->bind(SupplierRepo::class, SupplierRepoImpl::class);

         // Bind interfaces to implementations
         $this->app->bind(ItemsCsvUploadRepo::class, ItemsCsvUploadRepoImpl::class);
        $this->app->bind(ItemsRepo::class, ItemsRepoImpl::class);
        $this->app->bind(ItemsInventoryRepo::class, ItemsInventoryRepoImpl::class);
        $this->app->bind(ItemsReceiveRepo::class, ItemsReceiveRepoImpl::class);
        $this->app->bind(UnitRepo::class, UnitRepoImpl::class);
        $this->app->bind(UserRepo::class, UserRepoImpl::class);
        $this->app->bind(DeliveryChallanRepo::class, DeliveryChallanRepoImpl::class);
        $this->app->bind(ProductReturnRepo::class, ProductReturnRepoImpl::class);
        $this->app->bind(RequisitionRepo::class, RequisitionRepoImpl::class);
        $this->app->bind(QuotationRepo::class, QuotationRepoImpl::class);
        $this->app->bind(InventoryReportRepo::class, InventoryReportRepoImpl::class);
        $this->app->bind(InventoryBarcodeRepo::class, InventoryBarcodeRepoImpl::class);
        $this->app->bind(OrganizationRepo::class, OrganizationRepoImpl::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
