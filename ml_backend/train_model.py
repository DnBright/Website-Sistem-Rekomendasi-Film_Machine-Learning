import pandas as pd
import numpy as np
from sklearn.neighbors import NearestNeighbors
from scipy.sparse import csr_matrix
import pickle
import os

# Define paths
# Menggunakan dataset 20M yang sudah Anda sediakan di folder DATASET MOVIE LENS
DATA_DIR = '../DATASET MOVIE LENS'
MODELS_DIR = 'models'

def main():
    print("Loading data...")
    try:
        # Load datasets
        ratings = pd.read_csv(os.path.join(DATA_DIR, 'rating.csv'))
        movies = pd.read_csv(os.path.join(DATA_DIR, 'movie.csv'))
    except FileNotFoundError:
        print(f"Error: rating.csv or movie.csv not found in {DATA_DIR} folder.")
        return

    print(f"Original dataset shape: {ratings.shape}")
    
    print("Filtering data (Aggressive filtering to prevent Memory Errors for 20M dataset)...")
    
    # Karena dataset 20M sangat besar (700MB), kita harus melakukan filtering ketat
    # agar proses pembuatan Pivot Table tidak mengalami Out of Memory (RAM penuh).
    
    # 1. Hanya gunakan film yang sudah di-rating minimal 500 kali
    movie_counts = ratings['movieId'].value_counts()
    popular_movies = movie_counts[movie_counts >= 500].index
    ratings = ratings[ratings['movieId'].isin(popular_movies)]

    # 2. Hanya gunakan user yang memberikan rating minimal pada 100 film (active users)
    user_counts = ratings['userId'].value_counts()
    active_users = user_counts[user_counts >= 100].index
    ratings = ratings[ratings['userId'].isin(active_users)]
    
    # Jika masih terlalu besar, kita bisa memotong ke 20.000 user pertama saja
    # untuk mempercepat demo KNN
    users_subset = ratings['userId'].unique()[:10000]
    ratings = ratings[ratings['userId'].isin(users_subset)]

    print(f"Filtered dataset shape: {ratings.shape}")

    print("Constructing user-item pivot table...")
    # Create a pivot table (rows: userId, columns: movieId, values: rating)
    user_item_pivot = ratings.pivot(index='userId', columns='movieId', values='rating')

    print("Applying mean-centering...")
    # Mean-Centering: Subtract the user's mean rating from their ratings
    user_mean = user_item_pivot.mean(axis=1)
    user_item_normalized = user_item_pivot.sub(user_mean, axis=0).fillna(0)

    print("Converting to sparse matrix...")
    # Convert the normalized pivot table into a scipy.sparse.csr_matrix
    sparse_matrix = csr_matrix(user_item_normalized.values)

    print("Training KNN model...")
    # Initialize sklearn.neighbors.NearestNeighbors
    # n_neighbors=11 (K=10 plus the user itself)
    knn_model = NearestNeighbors(n_neighbors=11, metric='cosine', algorithm='brute')
    knn_model.fit(sparse_matrix)

    print("Exporting model and data...")
    os.makedirs(MODELS_DIR, exist_ok=True)
    model_data = {
        'knn_model': knn_model,
        'user_item_pivot': user_item_pivot,
        'user_item_normalized': user_item_normalized,
        'sparse_matrix': sparse_matrix,
        'movies_df': movies,
        'user_mean': user_mean
    }
    
    with open(os.path.join(MODELS_DIR, 'knn_model.pkl'), 'wb') as f:
        pickle.dump(model_data, f)
        
    print("Model trained and saved successfully in models/knn_model.pkl")

if __name__ == '__main__':
    main()
