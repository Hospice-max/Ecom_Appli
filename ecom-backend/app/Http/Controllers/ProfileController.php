<?php

namespace App\Http\Controllers;

use App\Events\UsersUpdated;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{


    public function users()
    {
        try {
            $users = User::where('id', '!=', Auth::id())
                         ->get();

            // Broadcast la liste mise à jour
            broadcast(new UsersUpdated(Auth::user(), $users))->toOthers();

            return response()->json([
                'success' => true,
                'users' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function user($id)
    {
        try {
            $user = User::where('id', $id)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'address' => $user->address,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateAvatar($id, Request $request) {
       try {
        $user = User::where('id', $id)->first();
        $user->avatar = $request->avatar;
        $user->save();
        return response()->json([
            'success' => true,
            'message' => 'Photo de profil mise à jour avec succès',
            'avatar' => $user->avatar
        ]);
       } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Une erreur est survenue',
            'error' => $e->getMessage()
        ], 500);
       }
    }

    public function updateProfile($id, Request $request) {
        try {
            $user = User::where('id', $id)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            $user->name = $request->name;
            $user->email = $request->email;
            $user->address = $request->address;
            $user->phone = $request->phone;
            $user->save();
            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::where('id', $id)->first();
            $user->delete();

             // Broadcast la liste mise à jour
             broadcast(new UsersUpdated(Auth::user(), $user))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Compte supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
