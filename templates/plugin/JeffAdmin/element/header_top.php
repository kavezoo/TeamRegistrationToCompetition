<?php
/**
 * Mobilos fejléc. A tagnál nincs oldalsáv, ezért a hamburger sem kell.
 */
if ((string)$this->request->getParam('prefix') === 'Member') {
    return;
}
?>
	  <header class="header-mobile d-block d-lg-none">
        <div class="header-mobile__bar">
          <div class="container-fluid">
            <div class="header-mobile-inner"><a class="logo" href="index.html"><img src="images/icon/logo.png" alt="CoolAdmin"></a>
              <button class="hamburger hamburger--slider" type="button" aria-label="Toggle navigation"><span class="hamburger-box"><span class="hamburger-inner"></span></span></button>
            </div>
          </div>
        </div>
        <nav class="navbar-mobile">
          <div class="container-fluid">
            <ul class="navbar-mobile__list list-unstyled">
              <li><a href="chart.html"><i class="fa-solid fa-chart-bar"></i>Charts</a></li>
              <li class="has-sub"><a class="js-arrow" href="#"><i class="fa-solid fa-table"></i>Tables</a>
                <ul class="navbar-mobile-sub__list list-unstyled js-sub-list">
                  <li><a href="table.html">Tables</a></li>
                  <li><a href="data-table.html">Data table</a></li>
                </ul>
              </li>
            </ul>
          </div>
        </nav>
      </header>
