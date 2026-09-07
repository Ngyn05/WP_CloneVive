with open('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/pages/us/product/vive-flow/overview/index.htm', 'r', encoding='utf-8') as f:
    html = f.read()

import re

old_block = """<div class="wording-area">
<div class="type20-pdp-title compatibility-title txt-color-white txt-m-color-black text-fix-widow">
                    Check your phone's compatibility
<div class="type20-pdp-title compatibility-title txt-color-white txt-m-color-black text-fix-widow">
                    Check your phone's compatibility
                  </div>
<div class="type20-pdp-copy compatibility-text txt-color-white txt-m-color-black text-fix-widow">
                    Pair a compatible phone with VIVE Flow to use it as a controller and take advantage of screen-sharing features. Search for your phone model below to see its functionality. 
                    <br/>
<a href="/us/vive-flow-phone-list/">
                       View compatible phone list &gt; \xa0
                    </a>
</div>
                    </div>"""

new_block = """<div class="wording-area">
<div class="type20-pdp-title compatibility-title txt-color-white txt-m-color-black text-fix-widow">
                    Check your phone's compatibility
                  </div>
<div class="type20-pdp-copy compatibility-text txt-color-white txt-m-color-black text-fix-widow">
                    Pair a compatible phone with VIVE Flow to use it as a controller and take advantage of screen-sharing features.
                    <br/>
<a href="/us/vive-flow-phone-list/">
                       View compatible phone list &gt; &nbsp;
                    </a>
</div>
<div class="type20-selling-info-disclaimer compatibility-disclaimer">
                      Users of incompatible devices can still enjoy VIVE Flow with the Controller for VIVE Flow.<sup>4</sup> Exynos-based Samsung phones running Firmware 1.24.623.02 or higher are now supported. Please check back as we update the compatible phone list.
                    </div>
</div>"""

if old_block in html:
    html = html.replace(old_block, new_block, 1)
    with open('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/pages/us/product/vive-flow/overview/index.htm', 'w', encoding='utf-8') as f:
        f.write(html)
    print("Successfully cleaned up wording area!")
else:
    print("Target block not found by exact string, trying regex...")
    pattern = r'<div class="wording-area">[\s\S]*?(?=<div class="fields-search-modal">)'
    html = re.sub(pattern, new_block + '\n', html, count=1)
    with open('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/pages/us/product/vive-flow/overview/index.htm', 'w', encoding='utf-8') as f:
        f.write(html)
    print("Successfully replaced with regex!")
