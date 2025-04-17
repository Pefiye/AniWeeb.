<?php
session_start();

if (!isset($_GET['id'])) {
    die("Anime ID not specified.");
}

$animeId = (int)$_GET['id'];

$query = '
query ($id: Int) {
  Media(id: $id, type: ANIME) {
    title {
      romaji
    }
    description(asHtml: false)
    coverImage {
      large
    }
    startDate {
      year
      month
      day
    }
    studios {
      nodes {
        name
      }
    }
    staff(sort: [RELEVANCE, ROLE]) {
      edges {
        node {
          name {
            full
          }
        }
        role
      }
    }
    trailer {
      id
      site
    }
  }
}';

$variables = ["id" => $animeId];

$options = [
    "http" => [
        "header" => "Content-Type: application/json\r\nAccept: application/json\r\n",
        "method" => "POST",
        "content" => json_encode([
            "query" => $query,
            "variables" => $variables
        ])
    ]
];

$context = stream_context_create($options);
$response = file_get_contents('https://graphql.anilist.co', false, $context);
if ($response === false) {
    die("Error fetching data.");
}

$result = json_decode($response, true);
$anime = $result['data']['Media'];

$title = $anime['title']['romaji'];
$description = strip_tags($anime['description']);
$coverImage = $anime['coverImage']['large'];
$releaseDate = $anime['startDate']['year'] . '-' . str_pad($anime['startDate']['month'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($anime['startDate']['day'], 2, '0', STR_PAD_LEFT);
$studio = $anime['studios']['nodes'][0]['name'] ?? 'Unknown';
$author = $anime['staff']['edges'][0]['node']['name']['full'] ?? 'Unknown';

$trailerEmbed = '';
if (isset($anime['trailer']['id']) && $anime['trailer']['site'] === 'youtube') {
    $trailerId = $anime['trailer']['id'];
    $trailerEmbed = "<iframe width='100%' height='315' src='https://www.youtube.com/embed/$trailerId' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>";
}

$isBookmarked = false;
if (isset($_SESSION['user_id'])) {
    require 'sql/db.php';
    $uid = $_SESSION['user_id'];
    $check = $conn->prepare("SELECT 1 FROM bookmarks WHERE user_id=? AND anime_id=?");
    $check->bind_param("ii", $uid, $animeId);
    $check->execute();
    $check->store_result();
    $isBookmarked = $check->num_rows > 0;
    $check->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="anime.css">
        <!-- ========================================================================= -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <!-- ========================================================================= -->

</head>
<body>
    <div class="vig"></div>
    <div id="trailer"></div>
    <div class="nav">
        <a href="Home.html" class="logo">ANIWEEB</a>
        <ul>
            <li><a href="discover.html">Discover</a></li>
            <li><a href="https://pefiye.github.io/Portfolio">About</a></li>
          </ul>
          <a href="sql/profile.php"><button>Profile</button></a>
    </div>

    <div class="main-content">
        <div class="left-column">
            <img class="cover-image" src="<?= htmlspecialchars($coverImage) ?>" alt="Anime Cover">
            <div class="info">
                <p><strong>Author:</strong> <?= htmlspecialchars($author) ?></p>
                <p><strong>Production:</strong> <?= htmlspecialchars($studio) ?></p>
                <p><strong>Release Date:</strong> <?= htmlspecialchars($releaseDate) ?></p>
                <p><strong>Voice Actor:</strong> TBD</p>
                <p><strong>More Info:</strong> TBD</p>
            </div>
        </div>

        <div class="center-column">
            <h1><?= htmlspecialchars($title) ?></h1>
            <p class="description"><?= htmlspecialchars($description) ?></p>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="post" action="toggle_bookmark.php" style="margin-top: 1em;">
                    <input type="hidden" name="anime_id" value="<?= $animeId ?>">
                    <input type="hidden" name="anime_title" value="<?= htmlspecialchars($title) ?>">
                    <input type="hidden" name="anime_cover" value="<?= htmlspecialchars($coverImage) ?>">
                </form>
            <?php endif; ?>
        </div>

        <div class="right-column">
            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="post" action="toggle_bookmark.php" style="margin-top: 1em;">
                  <div class="checkb">
                    <input class="checkboxx"type="checkbox" name="bookmark" onchange="this.form.submit()" <?= $isBookmarked ? 'checked' : '' ?>>
                    <label>
                        Bookmark
                    </label>
                    </div>
                    <input type="hidden" name="anime_id" value="<?= $animeId ?>">
                    <input type="hidden" name="anime_title" value="<?= htmlspecialchars($title) ?>">
                    <input type="hidden" name="anime_cover" value="<?= htmlspecialchars($coverImage) ?>">
                </form>
            <?php endif; ?>

            <?= $trailerEmbed ?: '<div class="screenshot-placeholder"><p><strong>Trailer Not Available</strong></p></div>' ?>
        </div>
    </div>

    <script>
        const trailer = document.getElementById("trailer"); 
        window.onmousemove = e => { 
            const x = e.clientX - trailer.offsetWidth / 5;
            const y = e.clientY - trailer.offsetHeight / 5; 
            trailer.style.transform = `translate(${x}px, ${y}px)`; 
        }
    </script>
</body>
</html>
