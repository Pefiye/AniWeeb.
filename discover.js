let currentPage = 1;
let isLoading = false;
let currentGenre = "";

async function fetchDiscoverAnime(page = 1, genres = []) {
  const grid = document.getElementById("animeGrid");
  if (!grid) {
    console.error("animeGrid container not found!");
    return;
  }

  if (page === 1) {
    grid.innerHTML = "";
  }

  const genreArray = Array.isArray(genres)
    ? genres.filter(g => g.trim() !== "")
    : genres && genres.trim() !== ""
      ? [genres]
      : [];

  const query = `
    query ($page: Int, $perPage: Int, $genre: [String]) {
      Page(page: $page, perPage: $perPage) {
        media(sort: POPULARITY_DESC, type: ANIME, genre_in: $genre) {
          id
          title {
            romaji
          }
          description(asHtml: false)
          coverImage {
            large
          }
        }
      }
    }
  `;

  const variables = {
    page: page,
    perPage: 18,
    ...(genreArray.length > 0 && { genre: genreArray })
  };

  isLoading = true;

  try {
    const response = await fetch("https://graphql.anilist.co", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({ query, variables }),
    });

    const json = await response.json();
    const results = json.data?.Page?.media;

    if (!results || results.length === 0) {
      grid.innerHTML = "<p>No anime found.</p>";
      return;
    }

    results.forEach(anime => {
      const card = document.createElement("form");
      card.className = "anime-tab";
      card.innerHTML = `
        <a href="anime.php?id=${anime.id}">
          <img src="${anime.coverImage.large}" alt="${anime.title.romaji}" class="anime-card-img">
          <h1>${anime.title.romaji}</h1>
          <h4>${stripTags(anime.description).slice(0, 100)}...</h4>
        </a>
      `;
      grid.appendChild(card);
    });

  } catch (err) {
    console.error("Failed to fetch anime:", err);
  }

  isLoading = false;
}

function stripTags(html) {
  const div = document.createElement("div");
  div.innerHTML = html;
  return div.textContent || div.innerText || "";
}

function handleScroll() {
  if (window.scrollY + window.innerHeight >= document.body.offsetHeight - 200 && !isLoading) {
    currentPage++;
    fetchDiscoverAnime(currentPage, currentGenre);
  }
}

async function searchAnime(query) {
  const searchQuery = `
    query ($search: String) {
      Page(page: 1, perPage: 18) {
        media(search: $search, type: ANIME, sort: SEARCH_MATCH) {
          id
          title {
            romaji
          }
          description(asHtml: false)
          coverImage {
            large
          }
        }
      }
    }
  `;

  const variables = { search: query };

  try {
    const response = await fetch("https://graphql.anilist.co", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({ query: searchQuery, variables }),
    });

    const json = await response.json();
    const grid = document.getElementById("animeGrid");
    grid.innerHTML = "";

    const results = json.data?.Page?.media;

    if (!results || results.length === 0) {
      grid.innerHTML = "<p>No results found.</p>";
      return;
    }

    results.forEach(anime => {
      const card = document.createElement("form");
      card.className = "anime-tab";
      card.innerHTML = `
        <a href="anime.php?id=${anime.id}">
          <img src="${anime.coverImage.large}" alt="${anime.title.romaji}" class="anime-card-img">
          <h1>${anime.title.romaji}</h1>
          <h4>${stripTags(anime.description).slice(0, 100)}...</h4>
        </a>
      `;
      grid.appendChild(card);
    });

  } catch (err) {
    console.error("Failed to search anime:", err);
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  const query = params.get("q");
  const genreFilter = document.getElementById("genreFilter");
  const searchForm = document.getElementById("animeSearchForm");
  const searchInput = document.getElementById("searchInput");

  if (query) {
    searchInput.value = query;
    searchAnime(query);
  } else {
    fetchDiscoverAnime(currentPage);
    window.addEventListener("scroll", handleScroll);
  }

  if (genreFilter) {
    genreFilter.addEventListener("change", () => {
      const selectedGenres = Array.from(genreFilter.selectedOptions).map(opt => opt.value);
      currentGenre = selectedGenres;
      currentPage = 1;
      fetchDiscoverAnime(currentPage, currentGenre);
    });
  }

  if (searchForm) {
    searchForm.addEventListener("submit", () => {
    });
  }
});
