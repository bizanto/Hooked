    <div id="top-area">
        <div class="toppad">
            <div id="topleft">
                <a href="/" id="hooked">Hooked</a>
            </div>
            <div id="topright">
                <?php if ($this->warp->modules->count('global')) : ?>
                <div id="global">
                    <?php echo $this->warp->modules->render('global'); ?>
                </div>
                <?php endif; ?>
                <?php if ($this->warp->modules->count('global-buttons')) : ?>
                <div id="global">
                    <?php echo $this->warp->modules->render('global-buttons'); ?>
               
                <?php endif; ?>
                <!--
                <ul class="tour">
                    <li><a class="tour" href="#"><span>Take the tour!</span></a>
                    <li><a class="download" href="#"><span>Download app now!</span></a>
                    <li><a class="signup" href="#"><span>Sign up today!</span></a>
                </ul>
               	-->
                </div>
            </div>
            <div class="clear"></div>
            
            <?php if ($this->warp->modules->count('homeslide')) : ?>
            	<?php echo $this->warp->modules->render('homeslide'); ?>
            <?php endif; ?>
              
        </div>
    </div>