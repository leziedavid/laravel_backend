<?php

namespace App\Http\Controllers\Api;

use App\Models\Invoice;
use App\Models\Listes;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function getInvoice($id)
    {
        Invoice::getInvoice($id);
    }

    public function pdfListeUsers()
    {
        Listes::ListeUsers();
    }
}
