<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Events\CartUpdated;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = Product::all();
            return response()->json([
                'success' => true,
                'products' => $products
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id, Request $request)
    {
        try {
            $product = Product::findOrFail($id);
            return response()->json([
                'success' => true,
                'product' => $product
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/products', $imageName);
                $validated['image'] = $imageName;
            }

            $product = Product::create($validated);

            broadcast(new \App\Events\ProductCreated($product))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Produit créé avec succès',
                'data' => $product
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Product $product)
    {
        try {
            $validated = $request->validate([
                'name' => 'string|max:255',
                'description' => 'string',
                'price' => 'numeric|min:0',
                'stock' => 'integer|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/products', $imageName);
                $validated['image'] = $imageName;
            }

            $product->update($validated);

            broadcast(new \App\Events\ProductUpdated($product))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Produit mis à jour avec succès',
                'data' => $product
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Product $product)
    {
        try {
            $product->delete();

            broadcast(new \App\Events\ProductDeleted($product))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Produit supprimé avec succès'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cart(Request $request)
    {
        try {
            $user = $request->user();
            $cart = $user->cart ?? [];

            return response()->json([
                'success' => true,
                'cart' => $cart
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addToCart(Request $request, Product $product)
    {
        try {
            $user = $request->user();
            $cart = $user->cart ?? [];

            // Vérifier si le produit est déjà dans le panier
            $existingItem = array_filter($cart, function($item) use ($product) {
                return $item['product_id'] == $product->id;
            });

            if (count($existingItem) > 0) {
                // Si le produit existe, augmenter la quantité
                foreach ($cart as &$item) {
                    if ($item['product_id'] == $product->id) {
                        $item['quantity'] += 1;
                    }
                }
            } else {
                // Sinon, ajouter le produit au panier
                $cart[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_price' => $product->price,
                    'quantity' => 1
                ];
            }

            $user->cart = $cart;
            $user->save();

            broadcast(new CartUpdated($user))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Produit ajouté au panier',
                'cart' => $cart
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function removeFromCart(Request $request, Product $product)
    {
        try {
            $user = $request->user();
            $cart = $user->cart ?? [];

            // Supprimer le produit du panier
            $cart = array_filter($cart, function($item) use ($product) {
                return $item['product_id'] != $product->id;
            });

            $user->cart = array_values($cart); // Réindexer le tableau
            $user->save();

            broadcast(new CartUpdated($user))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Produit retiré du panier',
                'cart' => $cart
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
