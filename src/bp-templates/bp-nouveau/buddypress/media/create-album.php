<?php
/**
 * BuddyBoss - Media Albums Create
 *
 * @since BuddyBoss 1.0.0
 */
?>

<div id="bp-media-create-album" style="display: none;">
    <transition name="modal">
        <div class="modal-mask bb-white bbm-model-wrap">
            <div class="modal-wrapper">
                <div id="boss-media-create-album-popup" class="modal-container">

                    <header class="bb-model-header">
                        <h4><?php _e( 'Create Album', 'buddyboss' ); ?></h4>
                        <a class="bb-model-close-button" id="bp-media-create-album-close" href="#"><span class="dashicons dashicons-no-alt"></span></a>
                    </header>

                    <div class="bb-field-wrap">
                        <label for="bb-album-title" class="bb-label"><?php _e( 'Title', 'buddyboss' ); ?></label>
                        <input id="bb-album-title" type="text" placeholder="<?php _e( 'Enter Album Title', 'buddyboss' ); ?>" />
                    </div>

                    <div class="bb-field-wrap">
                        <div class="media-uploader-wrapper">
                            <div class="dropzone" id="media-uploader"></div>
                        </div>
                    </div>

                    <footer class="bb-model-footer">
                        <?php if ( ! bp_is_group() ) : ?>
                            <div class="bb-dropdown-wrap">
                                <?php $privacy_options = BP_Media_Privacy::instance()->get_visibility_options(); ?>
                                <select id="bb-album-privacy">
                                    <?php foreach ( $privacy_options as $k => $option ) {
                                        ?>
                                        <option value="<?php echo $k; ?>"><?php echo $option; ?></option>
                                        <?php
                                    } ?>
                                </select>
                            </div>
                        <?php endif; ?>
                        <a class="button" id="bp-media-create-album-submit" href="#"><?php _e( 'Create Album', 'buddyboss' ); ?></a>
                    </footer>

                </div>
            </div>
        </div>
    </transition>
</div>