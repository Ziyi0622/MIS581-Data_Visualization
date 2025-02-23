<!DOCTYPE html>
<html>

<head>
    <title>FilmEpoch | Filtered Movies</title>
    <style>
        body {
            background-color: #29262E;
        }

        h1 {
            color: #f9f9f9;
            text-align: center;
            padding-top: 20px;
            margin: 0px;
            margin-bottom: 15px;
        }

        .function-container {
            display: flex;
            margin-bottom: 10px;
            padding-top: 10px;
        }

        .search-container {
            margin-left: 500px;
            margin-bottom: 5px;
            margin-top: 5px;
        }

        .sort-container {
            margin-left: 0px;
            margin-bottom: 5px;
            margin-top: 5px;
        }

        .search-bar {
            width: 250px;
            height: 25px;
            border-radius: 5px;
            border: none;
            font-size: 15px;
            font-family: 'Times New Roman', Times, serif;
            padding-left: 5px;
        }

        .search-button {
            width: 60px;
            height: 27px;
            border-radius: 5px;
            border: none;
            color: #29262E;
            font-size: 15px;
            font-family: 'Times New Roman', Times, serif;
            cursor: pointer;
        }

        .search-button:active {
            background-color: #e1e1e1;
            box-shadow: inset 0 1px 0 #ccc, 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .sort-container select {
            width: 150px;
            height: 27px;
            cursor: pointer;
        }

        .sortLabel {
            color: #f9f9f9;
            width: 100px;
            text-align: center;
            margin-left: 170px;
            font-size: 18px;
            margin-top: 8px;
            margin-right: -13px;
        }

        #notFoundMessage {
            color: #f9f9f9;
            font-size: 20px;
            padding-top: 20px;
        }

        .image-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            opacity: 1;
            transition: opacity 0.25s ease;
        }

        .image {
            margin: 10px;
            position: relative;
            transition: transform 0.25s ease;
        }

        .image:hover {
            transform: scale(1.1);
        }

        img {
            margin-left: 10px;
            margin-right: 10px;
            margin-bottom: 5px;
            margin-top: 10px;
            cursor: pointer;
        }

        #posterTitle {
            text-align: center;
            width: 210px;
            color: #f9f9f9;
            font-weight: bold;
            font-size: 18px;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 55.5%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            background-color: rgba(0, 0, 0, 0.8);
            color: #fff;
            padding: 20px;
            border-radius: 10px;
            max-width: 80%;
            max-height: 80%;
            overflow-y: auto;
        }

        .overlay-content {
            display: flex;
            align-items: flex-start;
        }

        .overlay img {
            max-width: 80%;
            height: auto;
            margin-right: 20px;
        }

        .movie-details {
            max-width: 50%;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 10px;
            color: #fff;
            cursor: pointer;
            font-size: 30px;
        }

        .info-row {
            display: flex;
            flex-direction: column;
        }

        .info-row p {
            margin: 5px 0;
        }

        .actors {
            white-space: nowrap;
            /* Prevent wrapping */
        }

        .imdb-link {
            color: #ffd700;
            text-decoration: none;
            font-weight: bold;
        }

        .imdb-link:hover {
            text-decoration: underline;
        }

        h3 {
            font-size: 30px;
            margin-top: 0px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div>
        <?php
        $startYear = isset($_GET['startYear']) ? intval($_GET['startYear']) : 1927;
        $endYear = isset($_GET['endYear']) ? intval($_GET['endYear']) : 2016;
        $genres = isset($_GET['genres']) ? $_GET['genres'] : [];
        $sort = isset($_GET['sort']) ? $_GET['sort'] : "title_year ASC";
        $search_query = isset($_GET['search']) ? $_GET['search'] : "";

        $title = "Movies From $startYear To $endYear";
        echo "<h1>$title</h1>";
        ?>
        <div class="function-container">
            <div class="search-container">
                <form onsubmit="event.preventDefault(); fetchMovies();">
                    <input type="text" name="search" class="search-bar" placeholder="Type movie name">
                    <button type="submit" class="search-button">Search</button>
                </form>
            </div>
            <div class="sortLabel">Sort By:</div>
            <div class="sort-container">
                <select name="sort" onchange="fetchMovies()">
                    <option value="title_year ASC">Year, Ascending</option>
                    <option value="title_year DESC">Year, Descending</option>
                    <option value="imdb_score ASC">IMDb Score, Ascending</option>
                    <option value="imdb_score DESC">IMDb Score, Descending</option>
                </select>
            </div>
        </div>

        <div class="image-container">
            <?php
            // Database connection parameters
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "Movie";

            // Create connection
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Query to select images and movie details from the table
            $sql = "SELECT DISTINCT image, movie_title, ROUND(title_year) AS title_year, imdb_score, duration, director_name, actor_1_name, actor_2_name, actor_3_name, movie_imdb_link, genres 
                    FROM CombinedTable 
                    WHERE title_year BETWEEN $startYear AND $endYear";

            // Add search query condition if it exists
            if (!empty($search_query)) {
                $sql .= " AND movie_title LIKE '%" . $conn->real_escape_string($search_query) . "%'";
            }

            // Add genres condition
            if (!empty($genres)) {
                $sql .= " AND (";
                foreach ($genres as $index => $genre) {
                    if ($index > 0) {
                        $sql .= " OR ";
                    }
                    $sql .= "genres LIKE '%" . $conn->real_escape_string($genre) . "%'";
                }
                $sql .= ")";
            }

            // Add sorting
            $sql .= " ORDER BY $sort";

            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                // Output data of each row
                while ($row = $result->fetch_assoc()) {
                    // Display image and overlay with movie details
                    echo '<div class="image">';
                    echo '<img src="data:image/jpg;base64,' . base64_encode($row['image']) . '" onclick="showOverlay(this)" 
                    data-title="' . htmlspecialchars($row['movie_title']) . ' (' . $row['title_year'] . ')" 
                    data-score="' . htmlspecialchars($row['imdb_score']) . '" 
                    data-duration="' . htmlspecialchars($row['duration']) . '" 
                    data-director="' . htmlspecialchars($row['director_name']) . '"
                    data-actor1="' . htmlspecialchars($row['actor_1_name']) . '"
                    data-actor2="' . htmlspecialchars($row['actor_2_name']) . '"
                    data-actor3="' . htmlspecialchars($row['actor_3_name']) . '"
                    data-link="' . htmlspecialchars($row['movie_imdb_link']) . '"
                    data-genres="' . htmlspecialchars($row['genres']) . '"
                    style="max-width: 100%; height: auto;">';
                    echo '<div id="posterTitle">' . htmlspecialchars($row['movie_title']) . '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p id="notFoundMessage">Movie not found in this time period.</p>';
            }

            // Close connection
            $conn->close();
            ?>

            <div id="overlay" class="overlay">
                <div class="overlay-content">
                    <span class="close" onclick="closeOverlay()">&times;</span>
                    <img id="overlay-image">
                    <div id="movie-info" class="movie-details"></div>
                </div>
            </div>

            <script>
                var startYear = <?php echo $startYear; ?>;
                var endYear = <?php echo $endYear; ?>;
                var genres = <?php echo json_encode($genres); ?>;

                function fetchMovies() {
                    var searchQuery = document.querySelector('.search-bar').value;
                    var sortOption = document.querySelector('.sort-container select').value;

                    var currentImageContainer = document.querySelector('.image-container');
                    currentImageContainer.style.opacity = '0';

                    currentImageContainer.addEventListener('transitionend', function updateContent() {
                        currentImageContainer.removeEventListener('transitionend', updateContent);

                        var xhr = new XMLHttpRequest();
                        var url = 'moviepics.php?ajax=1' +
                            '&search=' + encodeURIComponent(searchQuery) +
                            '&sort=' + encodeURIComponent(sortOption) +
                            '&startYear=' + encodeURIComponent(startYear) +
                            '&endYear=' + encodeURIComponent(endYear);

                        if (genres.length > 0) {
                            genres.forEach(function (genre) {
                                url += '&genres[]=' + encodeURIComponent(genre);
                            });
                        }

                        xhr.open('GET', url, true);
                        xhr.onreadystatechange = function () {
                            if (xhr.readyState == 4 && xhr.status == 200) {
                                var parser = new DOMParser();
                                var responseDoc = parser.parseFromString(xhr.responseText, 'text/html');

                                var newImageContainer = responseDoc.querySelector('.image-container');
                                currentImageContainer.innerHTML = newImageContainer.innerHTML;

                                // Reassign click event listeners to new images
                                var images = document.querySelectorAll('.image img');
                                images.forEach(function (img) {
                                    img.addEventListener('click', function () {
                                        showOverlay(this);
                                    });
                                });

                                // Step 3: Fade in the new content
                                currentImageContainer.style.opacity = '1';
                            }
                        };
                        xhr.send();
                    });
                }

                function showOverlay(img) {
                    var title = img.getAttribute('data-title');
                    var score = img.getAttribute('data-score');
                    var duration = img.getAttribute('data-duration');
                    var director = img.getAttribute('data-director');
                    var actor1 = img.getAttribute('data-actor1');
                    var actor2 = img.getAttribute('data-actor2');
                    var actor3 = img.getAttribute('data-actor3');
                    var link = img.getAttribute('data-link');
                    var genres = img.getAttribute('data-genres');

                    var overlay = document.getElementById('overlay');
                    var overlayImage = document.getElementById('overlay-image');
                    var movieInfo = document.getElementById('movie-info');
                    var imageSrc = img.src;
                    overlayImage.src = imageSrc;
                    movieInfo.innerHTML = '<h3>' + title + '</h3>' +
                        '<div class="info-row">' +
                        '<p>IMDb Score: ' + score + '</p>' +
                        '<p>Director: ' + director + '</p>' +
                        '<p class="actors">Actors: ' + actor1 + ', ' + actor2 + ', ' + actor3 + '</p>' +
                        '<p>Duration: ' + duration + '</p>' +
                        '<p>Genres: ' + genres + '</p>' +
                        '</div>' +
                        '<p><a href="' + link + '" target="_blank" class="imdb-link">IMDb Link</a></p>';
                    overlay.style.display = "block";

                    // Prevent click event from propagating to the document level
                    event.stopPropagation();
                }

                function closeOverlay() {
                    var overlay = document.getElementById('overlay');
                    overlay.style.display = "none";
                }

                // Listen for clicks on the document
                document.addEventListener('click', function (event) {
                    var overlay = document.getElementById('overlay');
                    var overlayContent = document.querySelector('.overlay-content');
                    if (!overlayContent.contains(event.target)) {
                        overlay.style.display = "none";
                    }
                });
            </script>

        </div>
    </div>
</body>


</html>