<?php

namespace App\Http\Controllers\Warga;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SanksiController extends Controller
{
    /**
     * Tampilkan fitur yang dibatasi, alasan, dan masa berlaku sanksi warga (PBI #48).
     */
    public function index(Request $request): View
    {
        $sanksiList = $request->user()->sanksi()
            ->with('riwayatModerasi')
            ->latest('mulai_pada')
            ->latest('id')
            ->get();

        return view('warga.sanksi.index', [
            'sanksiBerlaku' => $sanksiList->filter->isBerlaku()->values(),
            'riwayatSanksi' => $sanksiList->reject->isBerlaku()->values(),
        ]);
    }
}
