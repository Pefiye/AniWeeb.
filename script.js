async function fetchAniListData(query, variables) {
    const url = 'https://graphql.anilist.co';
    
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            query: query,
            variables: variables
        })
    });

    const json = await response.json();
    return json.data;
}

async function loadAnimeData() {
    const popularQuery = `
        query ($page: Int, $perPage: Int) {
            Page(page: $page, perPage: $perPage) {
                media(sort: POPULARITY_DESC, type: ANIME) {
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

    const finishedQuery = `
        query ($page: Int, $perPage: Int) {
            Page(page: $page, perPage: $perPage) {
                media(status: FINISHED, sort: END_DATE_DESC, type: ANIME) {
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

    const trailer = document.getElementById("trailer"); 
    window.onmousemove = e => { 
    const x = e.clientX -trailer.offsetWidth / 5;
    const y = e.clientY -trailer.offsetHeight / 5; 

    trailer.style.transform = `translate(${x}px, ${y}px)`; 
    }


    function closeburger()
    {
        let check = document.getElementById("checkb");
        check.checked = false;
    }

    const popular = await fetchAniListData(popularQuery, { page: 1, perPage: 10 });
    const finished = await fetchAniListData(finishedQuery, { page: 1, perPage: 10 });

    const highlight = popular.Page.media[0];
    document.querySelector(".highlight .fade-thing h1").textContent = highlight.title.romaji;
    document.querySelector(".highlight .fade-thing h4").textContent = highlight.description.replace(/<[^>]+>/g, '');
    document.querySelector(".highlight img").src = highlight.coverImage.large;

    const popularContainer = document.querySelector("#section1 .anime-tab-scroll");
    popular.Page.media.forEach(anime => {
        popularContainer.appendChild(createAnimeCard(anime));
    });

    const finishedContainer = document.querySelector("#section2 .anime-tab-scroll");
    finished.Page.media.forEach(anime => {
        finishedContainer.appendChild(createAnimeCard(anime));
    });
}

function createAnimeCard(anime) {
    const form = document.createElement('form');
    form.className = 'anime-tab';
    
    form.innerHTML = `
    <a href="anime.php?id=${anime.id}">
        <img src="${anime.coverImage.large}" alt="${anime.title.romaji}" class="anime-card-img"> 
        <h1>${anime.title.romaji}</h1>
        <h4>${anime.description.replace(/<[^>]+>/g, '').slice(0, 100)}...</h4>
    </a>
`;

        return form;
}


async function fetchRecentReleases() {
  const query = `
    query {
      Page(perPage: 10) {
        media(sort: START_DATE_DESC, type: ANIME, status: RELEASING) {
          id
          title {
            romaji
          }
          coverImage {
            extraLarge
          }
          description(asHtml: false)
        }
      }
    }
  `;

  const response = await fetch("https://graphql.anilist.co", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "Accept": "application/json",
    },
    body: JSON.stringify({ query }),
  });

  const { data } = await response.json();

  const container = document.querySelector("#section2 .anime-tab-scroll");
  container.innerHTML = "";

  data.Page.media.forEach(anime => {
    const card = document.createElement("form");
    card.className = "anime-tab";
    card.innerHTML = `
      <a href="anime.php?id=${anime.id}">
        <img src="${anime.coverImage.extraLarge}" alt="anime-img">
        <h1>${anime.title.romaji}</h1>
        <h4>${stripTags(anime.description).slice(0, 100)}...</h4>
      </a>
    `;
    container.appendChild(card);
  });
}

function stripTags(html) {
  let div = document.createElement("div");
  div.innerHTML = html;
  return div.textContent || div.innerText || "";
}

fetchRecentReleases();


document.addEventListener("DOMContentLoaded", loadAnimeData);