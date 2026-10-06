<?php

namespace App\Http\Controllers;

use App\Interfaces\Repo\Frontend\CategoryRepo;
use App\Interfaces\Repo\Frontend\CourseRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class FrontendHomeController extends Controller
{

    private $crsRepo;
    private $catRepo;

    public function __construct(CourseRepo $crsRepo, CategoryRepo $catRepo)
    {
        $this->crsRepo = $crsRepo;
        $this->catRepo = $catRepo;
    }

    public function index()
    {
        $courseData = $this->crsRepo->courselimitData($limit = 20);
        return view('frontend.home')
            ->with('course_info', $courseData);
    }

    public function loginCheck (Request $request) {
        echo student()->id.'<br>';
        echo student()->name.'<br>';
        echo student()->user_types.'<br>';
        echo '<a href="student-logout">Logout</a>';
    }
}
