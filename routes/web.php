<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/api/recommend', function (Request $request) {
    // Mock API response since Python backend is currently broken on the user's OS
    $userId = $request->input('user_id');
    
    // Create some dummy movie data
    return response()->json([
        'recommendations' => [
            ['movieId' => 1, 'title' => 'Toy Story (1995)', 'genres' => 'Adventure|Animation|Children|Comedy|Fantasy', 'predicted_rating' => 4.8],
            ['movieId' => 50, 'title' => 'The Usual Suspects (1995)', 'genres' => 'Crime|Mystery|Thriller', 'predicted_rating' => 4.6],
            ['movieId' => 2571, 'title' => 'Matrix, The (1999)', 'genres' => 'Action|Sci-Fi|Thriller', 'predicted_rating' => 4.5],
            ['movieId' => 296, 'title' => 'Pulp Fiction (1994)', 'genres' => 'Comedy|Crime|Drama|Thriller', 'predicted_rating' => 4.4],
            ['movieId' => 527, 'title' => 'Schindler\'s List (1993)', 'genres' => 'Drama|War', 'predicted_rating' => 4.3],
            ['movieId' => 1196, 'title' => 'Star Wars: Episode V - The Empire Strikes Back (1980)', 'genres' => 'Action|Adventure|Sci-Fi', 'predicted_rating' => 4.2],
            ['movieId' => 2858, 'title' => 'American Beauty (1999)', 'genres' => 'Drama|Romance', 'predicted_rating' => 4.1],
            ['movieId' => 1198, 'title' => 'Raiders of the Lost Ark (1981)', 'genres' => 'Action|Adventure', 'predicted_rating' => 4.0],
            ['movieId' => 318, 'title' => 'Shawshank Redemption, The (1994)', 'genres' => 'Crime|Drama', 'predicted_rating' => 3.9],
            ['movieId' => 593, 'title' => 'Silence of the Lambs, The (1991)', 'genres' => 'Crime|Horror|Thriller', 'predicted_rating' => 3.8],
        ]
    ]);
});
