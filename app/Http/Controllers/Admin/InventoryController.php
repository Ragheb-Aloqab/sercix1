<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\StoreInventoryItemRequest;
use App\Http\Requests\Admin\Inventory\UpdateInventoryItemRequest;
use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();

        $items = InventoryItem::query()
            ->when($q, function ($query) use ($q) {
                $query->where('name','like',"%{$q}%")
                      ->orWhere('sku','like',"%{$q}%")
                      ->orWhere('category','like',"%{$q}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.inventory.index', compact('items','q'));
    }

    public function create()
    {
        return view('admin.inventory.create');
    }

    public function store(StoreInventoryItemRequest $request)
    {
        InventoryItem::create($request->validated());

        return redirect()
            ->route('admin.inventory.index')
            ->with('success', 'تم إضافة عنصر للمخزون.');
    }

    public function edit(InventoryItem $inventory) // resource name = inventory
    {
        $item = $inventory;
        return view('admin.inventory.edit', compact('item'));
    }

    public function update(UpdateInventoryItemRequest $request, InventoryItem $inventory)
    {
        $inventory->update($request->validated());

        return redirect()
            ->route('admin.inventory.index')
            ->with('success', 'تم تحديث عنصر المخزون.');
    }

    public function destroy(InventoryItem $inventory)
    {
        $inventory->delete();

        return back()->with('success', 'تم حذف عنصر المخزون.');
    }
}
