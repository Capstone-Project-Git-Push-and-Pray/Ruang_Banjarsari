<?php

namespace App\Http\Controllers;

use App\Models\Lampiran;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LampiranController extends Controller
{
    /**
     * Tampilkan berkas foto lampiran kepada pengguna yang berhak (PBI #25, PBI #38).
     * Lampiran yang tidak boleh dilihat diperlakukan seolah tidak ada (404).
     */
    public function show(Lampiran $lampiran): StreamedResponse
    {
        abort_unless(Gate::allows('view', $lampiran), 404);

        $disk = Storage::disk(Lampiran::DISK);
        abort_unless($disk->exists($lampiran->path), 404);

        return $disk->response($lampiran->path, headers: ['Cache-Control' => 'private, no-store']);
    }
}
