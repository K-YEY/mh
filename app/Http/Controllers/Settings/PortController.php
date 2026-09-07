<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Port;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortController extends Controller
{
    public function index(): Response
    {
        $ports = Port::orderBy('code', 'asc')->get();

        return Inertia::render('Settings/Ports/Index', [
            'ports' => $ports,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|size:5|unique:ports,code',
            'name' => 'required|string',
            'country_code' => 'required|string|size:2',
            'is_active' => 'boolean',
        ]);

        Port::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'country_code' => strtoupper($validated['country_code']),
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Ocean seaport added successfully.');
    }

    public function update(Request $request, Port $port): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|size:5|unique:ports,code,'.$port->id,
            'name' => 'required|string',
            'country_code' => 'required|string|size:2',
            'is_active' => 'required|boolean',
        ]);

        $port->update([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'country_code' => strtoupper($validated['country_code']),
            'is_active' => (bool) $validated['is_active'],
        ]);

        return redirect()->back()->with('success', 'تم تحديث بيانات الميناء بنجاح.');
    }

    public function destroy(Port $port): RedirectResponse
    {
        $port->delete();

        return redirect()->back()->with('success', 'تم حذف الميناء بنجاح.');
    }
}
