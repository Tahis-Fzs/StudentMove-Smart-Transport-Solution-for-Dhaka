<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class OfferController extends Controller
{
    /**
     * Display all offers
     */
    public function index(): View
    {
        $offers = Offer::latest()->paginate(15);
        return view('admin.offers.index', compact('offers'));
    }

    /**
     * Show create form
     */
    public function create(): View
    {
        return view('admin.offers.create');
    }

    /**
     * Store new offer
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after:valid_from'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        Offer::create([
            'title' => $request->title,
            'description' => $request->description,
            'discount_percentage' => $request->discount_percentage ?? 0,
            'valid_from' => $request->valid_from,
            'valid_until' => $request->valid_until,
            'is_active' => $request->has('is_active'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.offers.index')->with('success', 'Offer created successfully!');
    }

    /**
     * Show edit form
     */
    public function edit(Offer $offer): View
    {
        return view('admin.offers.edit', compact('offer'));
    }

    /**
     * Update offer
     */
    public function update(Request $request, Offer $offer): RedirectResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after:valid_from'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $offer->update([
            'title' => $request->title,
            'description' => $request->description,
            'discount_percentage' => $request->discount_percentage ?? 0,
            'valid_from' => $request->valid_from,
            'valid_until' => $request->valid_until,
            'is_active' => $request->has('is_active'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.offers.index')->with('success', 'Offer updated successfully!');
    }

    /**
     * Delete offer
     */
    public function destroy(Offer $offer): RedirectResponse
    {
        $offer->delete();
        return redirect()->route('admin.offers.index')->with('success', 'Offer deleted successfully!');
    }
}

