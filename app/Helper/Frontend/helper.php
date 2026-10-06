<?php
function student()
{
    return Auth::guard('student')->user();
}
