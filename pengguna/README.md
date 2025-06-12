# CineFind - Movie Recommendation Platform

CineFind is a Netflix-like movie recommendation platform that helps users discover new movies based on ratings and user preferences. The application integrates with The Movie Database (TMDB) API to provide comprehensive movie information and recommendations.

## Features

- 🎬 Movie Recommendations based on user ratings
- 🔍 Advanced search functionality
- 🎯 Filter movies by genre, year, and rating
- 👤 User authentication
- 📝 Watchlist management
- 📱 Responsive design for all devices
- 🎥 Movie trailers and detailed information
- ⭐ Rating system

## Prerequisites

- A modern web browser
- TMDB API key (get one at [TMDB](https://www.themoviedb.org/documentation/api))

## Setup Instructions

1. Clone the repository:
```bash
git clone https://github.com/yourusername/cinefind.git
cd cinefind
```

2. Get your TMDB API key:
   - Go to [TMDB](https://www.themoviedb.org/documentation/api)
   - Sign up for an account
   - Request an API key
   - Copy your API key

3. Configure the API key:
   - Open `script.js`
   - Replace `'YOUR_TMDB_API_KEY'` with your actual TMDB API key

4. Open the application:
   - Open `index.html` in your web browser
   - Or use a local server (recommended)

## Using a Local Server

You can use Python's built-in HTTP server:

```bash
# Python 3
python -m http.server 8000

# Python 2
python -m SimpleHTTPServer 8000
```

Then open `http://localhost:8000` in your browser.

## Features in Detail

### Movie Recommendations
- Browse featured movies
- Get personalized recommendations based on your watchlist and ratings
- View top-rated movies

### Search and Filter
- Search movies by title, actor, or director
- Filter by:
  - Genre (Action, Comedy, Drama, etc.)
  - Release Year
  - Rating

### User Features
- Create an account
- Save movies to your watchlist
- Rate and review movies
- Get personalized recommendations

### Movie Details
- View detailed information about each movie
- Watch trailers
- See cast and crew information
- Read user reviews and ratings

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- [TMDB](https://www.themoviedb.org/) for providing the movie data API
- [Font Awesome](https://fontawesome.com/) for icons
- Netflix for design inspiration 