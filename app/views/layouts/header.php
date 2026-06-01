<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AgriLocal</title>
  <link rel="icon" type="image/png" href="assets/img/logo.png">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;500;600;700&family=Noto+Serif:wght@400;600&family=Material+Icons&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="assets/css/screen.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="main-nav">
  <div class="nav-container">
    <div class="logo">
      <img src="assets/img/logo.png" alt="AgriLocal" style="height:44px;width:44px;object-fit:contain;vertical-align:middle;">
      <a href="index.php?url=home">AgriLocal</a>
    </div>

    <div class="nav-links">
      <a href="index.php?url=home">Home</a>
      <a href="index.php?url=products">Products</a>
      <a href="index.php?url=vendors">Vendors</a>
    </div>

    <div class="search-bar">
      <span class="material-icons">search</span>
      <input type="text" placeholder="Search vendors or products" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" name="search_query" readonly onfocus="this.removeAttribute('readonly')">
    </div>

    <div class="nav-icons">
      <a href="#" class="nav-icon-item">
        <span class="material-icons">favorite_border</span>
        <span class="icon-label">Favorites</span>
      </a>

      <a href="index.php?url=cart" class="nav-icon-item">
        <span class="material-icons">shopping_cart</span>
        <span class="icon-label">Cart</span>
      </a>

      <div class="nav-icon-item account-trigger" id="accountTrigger" role="button" tabindex="0">
        <span class="material-icons">person_outline</span>
        <span class="icon-label">Account</span>

        <div class="account-dropdown-menu" id="accountDropdown">
          <a href="index.php?url=profile">My Account</a>
          <a href="#">My Orders</a>
          <form method="POST" action="index.php?url=auth/logout">
            <button type="submit">Logout</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</nav>