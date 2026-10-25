<?php include 'component/header.php'; ?>
<!-- Index Main Page Home -->
<body>
  <div class="site-wrapper">
    <div class="site-wrapper-inner">
      <div class="main-container">
        <div class="inner cover">
	 <center><img src=https://1.bp.blogspot.com/-mWuZQfYD_b0/VEOrzUtstdI/AAAAAAAASjg/OfAYrybpCyo/s1600/LOGO%2BUNIVERSITAS%2BNEGERI%2BGORONTALO.png height=200px width=200px></center>
            <h1>WHOis lo PustiKOM UNG punya...</h1>
              <h4>kalau mo ba cari ip/domain ngana input dibawah jo...</h4>
              <div class="row">
            <div class="col-lg-12">
          <form action="<?=$_SERVER['PHP_SELF'];?>">
        <div class="input-group input-group-lg">
      <input type="text" class="form-control"  placeholder="kase maso nt pe ip / domain disini..." name="domain" id="domain" value="<?=$domain;?>"> 
    <span class="input-group-btn">
  <input type="submit" class="btn btn-shorten" value="klik disini, kalu somo ba cari!" title="WHOis UNG!">
</span>
</form>
  </div>
  <br>
    </div>
      <?php include 'config/result.php'; ?>
            <div class="col-lg-12">
              <div id="link"></div>
            </div>
          </div>
        <?php include 'component/footer.php'; ?>
