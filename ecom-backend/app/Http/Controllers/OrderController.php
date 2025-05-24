<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->orders()->latest()->get();
        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'products' => 'required|array',
            'address' => 'required|string',
            'total' => 'required|numeric'
        ]);

        $order = $request->user()->orders()->create([
            'address' => $validated['address'],
            'total' => $validated['total'],
            'status' => 'en_attente'
        ]);

        // Ajouter les produits à la commande
        foreach ($validated['products'] as $product) {
            $order->products()->attach($product);
        }

        return response()->json([
            'success' => true,
            'message' => 'Commande créée avec succès',
            'order' => $order
        ]);
    }
}
