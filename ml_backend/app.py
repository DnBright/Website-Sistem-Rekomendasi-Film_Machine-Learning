from flask import Flask, request, jsonify
from flask_cors import CORS
import pickle
import numpy as np
import pandas as pd
import os

app = Flask(__name__)
CORS(app)

MODEL_PATH = os.path.join('models', 'knn_model.pkl')

# Load model data on startup
print("Loading model...")
try:
    with open(MODEL_PATH, 'rb') as f:
        model_data = pickle.load(f)
    knn_model = model_data['knn_model']
    user_item_pivot = model_data['user_item_pivot']
    user_item_normalized = model_data['user_item_normalized']
    sparse_matrix = model_data['sparse_matrix']
    movies_df = model_data['movies_df']
    user_mean = model_data['user_mean']
    print("Model loaded successfully.")
except FileNotFoundError:
    print(f"Warning: {MODEL_PATH} not found. Please run train_model.py first.")
    knn_model = None

def get_movie_recommendations(user_id, top_n=10):
    if user_id not in user_item_normalized.index:
        return {"error": "User not found in the dataset."}

    # Find the user's index in the normalized matrix
    user_index = user_item_normalized.index.get_loc(user_id)
    user_vector = user_item_normalized.iloc[user_index, :].values.reshape(1, -1)

    # Get the K-nearest neighbors and their cosine distances
    distances, indices = knn_model.kneighbors(user_vector, n_neighbors=11)

    # Exclude the target user from the neighbors list (distance = 0)
    # The first result is usually the user themselves
    distances = distances.flatten()[1:]
    indices = indices.flatten()[1:]

    # Convert distances to similarities (similarity = 1 - distance)
    similarities = 1 - distances
    
    # Get neighbors' user IDs
    neighbor_ids = user_item_normalized.index[indices]

    # Filter out movies the target user has already watched
    user_watched = user_item_pivot.loc[user_id].dropna().index
    
    # Calculate predicted ratings for remaining movies
    movie_ids = user_item_normalized.columns
    unwatched_movies = [mid for mid in movie_ids if mid not in user_watched]
    
    predictions = []
    
    # Extract neighbor ratings for efficiency
    neighbor_ratings = user_item_normalized.loc[neighbor_ids]
    
    for movie_id in unwatched_movies:
        # Neighbor ratings for this movie
        movie_ratings = neighbor_ratings[movie_id].values
        
        # Using weighted average of normalized ratings
        if np.sum(similarities) > 0:
            weighted_sum = np.dot(similarities, movie_ratings)
            predicted_norm_rating = weighted_sum / np.sum(similarities)
            # Add user's mean to get actual predicted rating
            predicted_rating = predicted_norm_rating + user_mean.loc[user_id]
            predictions.append((movie_id, predicted_rating))
    
    # Sort and return top n movies
    predictions.sort(key=lambda x: x[1], reverse=True)
    top_predictions = predictions[:top_n]
    
    recommendations = []
    for mid, pred_rating in top_predictions:
        # Retrieve movie info
        movie_info_matches = movies_df[movies_df['movieId'] == mid]
        if not movie_info_matches.empty:
            movie_info = movie_info_matches.iloc[0]
            recommendations.append({
                'movieId': int(mid),
                'title': str(movie_info['title']),
                'genres': str(movie_info['genres']),
                'predicted_rating': round(float(pred_rating), 2)
            })
        
    return {"recommendations": recommendations}

@app.route('/api/recommend', methods=['POST'])
def recommend():
    if knn_model is None:
        return jsonify({"error": "Model is not loaded. Please train the model first."}), 500
        
    data = request.get_json()
    if not data or 'user_id' not in data:
        return jsonify({"error": "Please provide user_id"}), 400
        
    user_id = data.get('user_id')
    top_n = data.get('top_n', 10)
    
    try:
        user_id = int(user_id)
        top_n = int(top_n)
    except ValueError:
        return jsonify({"error": "user_id and top_n must be integers"}), 400
        
    result = get_movie_recommendations(user_id, top_n)
    
    if "error" in result:
        return jsonify(result), 404
        
    return jsonify(result)

@app.route('/api/movies', methods=['GET'])
def get_movies():
    if knn_model is None:
        return jsonify({"error": "Model is not loaded."}), 500
        
    # Return a list of all available movies
    movies_list = movies_df[['movieId', 'title', 'genres']].to_dict('records')
    return jsonify({"movies": movies_list})

if __name__ == '__main__':
    # Run the app
    app.run(debug=True, port=5000)
