<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Rekomendasi Film KNN</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }
        
        .animated-bg {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }
    </style>
</head>
<body class="antialiased bg-slate-50 text-slate-800 selection:bg-indigo-500 selection:text-white font-['Instrument_Sans'] min-h-screen flex flex-col relative overflow-x-hidden">
    
    <!-- Background Decorators -->
    <div class="fixed inset-0 z-[-1] animated-bg opacity-10"></div>
    <div class="fixed top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-purple-400 blur-[120px] opacity-20 pointer-events-none"></div>
    <div class="fixed bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-indigo-500 blur-[120px] opacity-20 pointer-events-none"></div>

    <!-- Navigation -->
    <nav class="w-full py-6 px-8 flex items-center justify-between z-10 relative max-w-7xl mx-auto">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-indigo-500/30">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
            </div>
            <span class="font-bold text-xl tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-indigo-700 to-purple-700">MovieRecs</span>
        </div>
        <div class="hidden md:flex gap-8 text-sm font-semibold text-slate-600">
            <a href="#try-it" class="hover:text-indigo-600 transition-colors">Coba Demo</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <main class="flex-grow flex flex-col items-center px-4 pt-16 pb-24 z-10 relative max-w-7xl mx-auto w-full">
        <div class="text-center max-w-3xl mx-auto space-y-6 mb-16 animate-fade-in-up">
            <div class="inline-block px-4 py-1.5 rounded-full bg-indigo-100 text-indigo-700 font-semibold text-xs tracking-wider uppercase mb-4 shadow-sm border border-indigo-200">
                Machine Learning Powered
            </div>
            <h1 class="text-5xl md:text-7xl font-bold tracking-tight text-slate-900 leading-[1.1]">
                Temukan Film Favorit <br/> 
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">Selanjutnya</span>
            </h1>
            <p class="text-lg md:text-xl text-slate-600 leading-relaxed max-w-2xl mx-auto mt-6">
                Sistem rekomendasi cerdas berbasis <strong>User-Item Collaborative Filtering</strong>. Algoritma KNN mempelajari selera tontonan Anda dan memberikan rekomendasi film yang dipersonalisasi.
            </p>
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="#try-it" class="px-8 py-4 rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold shadow-md hover:shadow-xl hover:shadow-indigo-500/30 hover:scale-105 transition-all duration-300">
                    Coba Rekomendasi
                </a>
            </div>
        </div>

        <!-- Application Interface (Glass Card) -->
        <div id="try-it" class="w-full max-w-5xl glass-panel rounded-3xl p-8 md:p-12 mt-8 relative overflow-hidden animate-fade-in-up" style="animation-delay: 0.2s; opacity: 0; animation-fill-mode: forwards;">
            <!-- Decorative accent -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-indigo-100 to-transparent rounded-bl-full opacity-50 pointer-events-none"></div>
            
            <div class="relative z-10">
                <div class="text-center mb-10">
                    <h2 class="text-3xl font-bold text-slate-800 mb-3">Mulai Dapatkan Rekomendasi</h2>
                    <p class="text-slate-500">Masukkan ID Pengguna dari Dataset MovieLens (Misal: 1 - 600) untuk melihat film yang disarankan.</p>
                </div>

                <form id="recommend-form" class="flex flex-col md:flex-row gap-4 justify-center items-center max-w-2xl mx-auto mb-16">
                    <div class="relative w-full md:w-auto flex-grow">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </div>
                        <input type="number" id="userId" min="1" max="1000" required
                            class="w-full pl-12 pr-4 py-4 rounded-2xl border border-slate-200 bg-white/80 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 shadow-sm text-slate-900 font-medium placeholder:text-slate-400 transition-all outline-none" 
                            placeholder="Masukkan User ID (Contoh: 15)">
                    </div>
                    <button type="submit" id="submit-btn" class="w-full md:w-auto px-8 py-4 rounded-2xl bg-slate-900 text-white font-bold shadow-md hover:bg-slate-800 hover:shadow-lg hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 group whitespace-nowrap cursor-pointer">
                        <span id="btn-text">Rekomendasikan</span>
                        <svg id="btn-icon" class="w-5 h-5 group-hover:translate-x-1 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                        
                        <!-- Spinner (hidden by default) -->
                        <svg id="btn-spinner" class="animate-spin hidden h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </form>

                <!-- Status Messages -->
                <div id="error-message" class="hidden max-w-2xl mx-auto mb-8 p-4 rounded-2xl bg-red-50 text-red-600 border border-red-100 flex items-center gap-3">
                    <svg class="w-6 h-6 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <p id="error-text" class="font-medium"></p>
                </div>

                <!-- Results Grid -->
                <div id="results-container" class="hidden">
                    <div class="flex items-center justify-between mb-6 border-b border-slate-200 pb-4">
                        <h3 class="text-xl font-bold text-slate-800">Top 10 Rekomendasi</h3>
                        <span id="result-user-label" class="text-sm font-medium px-4 py-1.5 bg-indigo-50 text-indigo-700 border border-indigo-100 rounded-full">User ID: -</span>
                    </div>
                    
                    <div id="movies-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Movie Cards will be inserted here via JS -->
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full py-8 text-center text-slate-500 text-sm mt-auto z-10 relative">
        <p>Proyek Sistem Rekomendasi Film Berbasis User-Item Collaborative Filtering (KNN)</p>
    </footer>

    <!-- Template for Movie Card -->
    <template id="movie-card-template">
        <div class="movie-card group bg-white/90 backdrop-blur-sm rounded-2xl border border-slate-100 overflow-hidden shadow-sm hover:shadow-xl hover:shadow-indigo-500/10 transition-all duration-300 transform hover:-translate-y-1 flex flex-col h-full opacity-0" style="animation-fill-mode: forwards;">
            <div class="h-40 bg-slate-100 relative overflow-hidden flex items-center justify-center p-6 text-center">
                <!-- Movie Poster Placeholder with dynamic gradient -->
                <div class="absolute inset-0 opacity-20 bg-gradient-to-br from-indigo-500 to-purple-600"></div>
                <svg class="w-12 h-12 text-indigo-300 relative z-10 group-hover:scale-110 transition-transform duration-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon><line x1="8" y1="2" x2="8" y2="18"></line><line x1="16" y1="6" x2="16" y2="22"></line></svg>
            </div>
            <div class="p-6 flex-grow flex flex-col">
                <div class="flex items-start justify-between gap-4 mb-3">
                    <h4 class="font-bold text-lg text-slate-900 leading-tight movie-title">Movie Title</h4>
                </div>
                <div class="flex flex-wrap gap-1.5 mb-5 movie-genres">
                    <!-- Genres injected here -->
                </div>
                <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Prediksi Rating</span>
                    <div class="flex items-center gap-1.5 bg-yellow-50 px-2.5 py-1 rounded-full border border-yellow-100">
                        <svg class="w-4 h-4 text-yellow-500 fill-yellow-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        <span class="font-bold text-yellow-700 text-sm movie-rating">4.5</span>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('recommend-form');
            const userIdInput = document.getElementById('userId');
            const btnText = document.getElementById('btn-text');
            const btnIcon = document.getElementById('btn-icon');
            const btnSpinner = document.getElementById('btn-spinner');
            const submitBtn = document.getElementById('submit-btn');
            
            const resultsContainer = document.getElementById('results-container');
            const moviesGrid = document.getElementById('movies-grid');
            const resultUserLabel = document.getElementById('result-user-label');
            
            const errorMsg = document.getElementById('error-message');
            const errorText = document.getElementById('error-text');
            const template = document.getElementById('movie-card-template');

            const FLASK_API_URL = 'http://127.0.0.1:5000/api/recommend';

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const userId = userIdInput.value;
                if (!userId) return;

                // Set loading state
                submitBtn.disabled = true;
                btnText.textContent = 'Memproses...';
                btnIcon.classList.add('hidden');
                btnSpinner.classList.remove('hidden');
                
                // Hide previous results/errors
                resultsContainer.classList.add('hidden');
                errorMsg.classList.add('hidden');
                moviesGrid.innerHTML = '';

                try {
                    const response = await fetch(FLASK_API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            user_id: parseInt(userId),
                            top_n: 10
                        })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.error || 'Terjadi kesalahan pada server');
                    }

                    // Success - Render results
                    renderMovies(data.recommendations, userId);
                    
                } catch (error) {
                    // Show error
                    let message = error.message;
                    if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
                        message = 'Gagal terhubung ke API Backend. Pastikan Flask server (app.py) sudah berjalan di port 5000.';
                    }
                    
                    errorText.textContent = message;
                    errorMsg.classList.remove('hidden');
                } finally {
                    // Reset button state
                    submitBtn.disabled = false;
                    btnText.textContent = 'Rekomendasikan';
                    btnIcon.classList.remove('hidden');
                    btnSpinner.classList.add('hidden');
                }
            });

            function renderMovies(movies, userId) {
                resultUserLabel.textContent = `User ID: ${userId}`;
                
                movies.forEach((movie, index) => {
                    const clone = template.content.cloneNode(true);
                    
                    // Add animation with stagger
                    const cardDiv = clone.querySelector('.movie-card');
                    cardDiv.classList.add('animate-fade-in-up');
                    cardDiv.style.animationDelay = `${(index * 0.1) + 0.1}s`;

                    clone.querySelector('.movie-title').textContent = movie.title;
                    
                    // Fix rating formatting
                    clone.querySelector('.movie-rating').textContent = Number(movie.predicted_rating).toFixed(1);
                    
                    const genresContainer = clone.querySelector('.movie-genres');
                    
                    if (movie.genres === '(no genres listed)') {
                        const span = document.createElement('span');
                        span.className = 'text-[11px] font-semibold tracking-wide uppercase px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 border border-slate-200';
                        span.textContent = 'Unknown';
                        genresContainer.appendChild(span);
                    } else {
                        const genresList = movie.genres.split('|');
                        
                        genresList.slice(0, 3).forEach(genre => { 
                            const span = document.createElement('span');
                            span.className = 'text-[11px] font-semibold tracking-wide uppercase px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-600 border border-indigo-100';
                            span.textContent = genre;
                            genresContainer.appendChild(span);
                        });
                        
                        if (genresList.length > 3) {
                            const span = document.createElement('span');
                            span.className = 'text-[11px] font-semibold tracking-wide px-1.5 py-0.5 rounded-md bg-slate-50 text-slate-500 border border-slate-200';
                            span.textContent = `+${genresList.length - 3}`;
                            genresContainer.appendChild(span);
                        }
                    }

                    moviesGrid.appendChild(clone);
                });

                resultsContainer.classList.remove('hidden');
                
                setTimeout(() => {
                    resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 100);
            }
        });
    </script>
</body>
</html>
