<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product; // Import Product model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Import Log facade
use Illuminate\Support\Facades\DB; // Import DB facade

class CartController extends Controller
{
    // Get the cart items for the logged-in user
    public function index()
    {
        $userId = Auth::id();
        Log::info("User ID: $userId fetching cart items."); // Log user ID

        $cart = Cart::where('user_id', $userId)->with('items.product')->first(); // Eager load product information

        Log::info("Cart retrieved: ", [$cart]); // Log the retrieved cart

        return response()->json($cart);
    }

    // Add an item to the cart
    public function store(Request $request, $id) // Receive product ID from the URL
    {
        Log::info("Attempting to add product ID: $id to cart."); // Log the attempt

        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $userId = Auth::id(); // Get the authenticated user's ID
        Log::info("User ID:  $userId."); // Log cart information

        $cart = Cart::firstOrCreate(['user_id' => $userId]);

        Log::info("Cart ID: {$cart->id} for user ID: $userId."); // Log cart information

        $product = Product::findOrFail($id); // Fetch the product by ID
        Log::info("Product retrieved: ", [$product]); // Log the product details

        // Check if the item already exists in the cart
        $cartItem = CartItem::updateOrCreate(
            [
                'cart_id' => $cart->id,
                'product_id' => $product->id,
            ],
            [
                'quantity' => $request->quantity,
                'price' => $product->price, // Add price from product
                'image' => $product->image, // Optionally use the product's image
            ]
        );

        Log::info("Cart item added or updated: ", [$cartItem]); // Log the cart item details

        return response()->json($cartItem, 201);
    }

    // Remove an item from the cart
    public function destroy($id)
    {
        Log::info("Attempting to remove cart item ID: $id."); // Log the removal attempt

        $cartItem = CartItem::findOrFail($id); // Find cart item by its ID
        $cartItem->delete();

        Log::info("Cart item ID: $id deleted."); // Log the successful deletion

        return response()->json(null, 204);
    }




}
