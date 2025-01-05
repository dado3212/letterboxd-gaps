# letterboxd-stats
PHP website for calculating some Letterboxd stats


TODO:
- Put in username or drag-drop files (full .zip? watchlist? other?)
Show
- Most liked
- Likes/time
- Follower graph (3js?)
- Follower recommendation
- Women directions
- Languages
- Countries

SVG map - https://stephanwagner.me/create-world-map-charts-with-svgmap#svgMapDemoGDP, https://github.com/StephanWagner/svgMap?tab=readme-ov-file, https://www.cssscript.com/demo/interactive-svg-world-map/

Misc
- Make the drag/drop also selectable
- TMDB attribution in the UI
- Letterboxd attribution in the UI
- The Remains of the Day	1993	2048	1245	tt0107943	https://boxd.it/291A	0	en	/uDGDtqSvuch324WnM7Ukdp1bCAQ.jpg	["US", ""]	pending

**secret.php**
```
define('TMDB_API_KEY', '<key>');
```

https://github.com/keplerg/color-extract/blob/main/index.php

Color Science:
Dominant color via dominant hue - https://www.sciencedirect.com/science/article/pii/S0042698922000840?pes=vor&utm_source=wiley&getft_integrator=wiley

https://onlinelibrary.wiley.com/doi/10.1002/col.22485

25 perceptually distinct colors, which were also displayed in pseudorandom order. These colors were extracted from images using the k-mean clustering algorithm in CAM02-UCS color space. Initial cluster centroid positions (seeds) were selected uniformly at random from the image's color gamut, while the resulting colors were snapped to the nearest color in the image. By using uniformly selected seeds, we were able to extract colors of all color categories in the image. We have tested different sizes of extracted colors (10, 15, 25, 35, 50) and come to the conclusion that 25 colors are sufficient so that no key color is missing and resulted colors are not too similar.

We used parameters recommended by Moroney40 for transforming images from sRGB into CIECAM02 coordinates: LA = 4, white point = D65, Yb = 20, and dim surround.

In total, we computed 137 features, which can be subcategorized into six categories that according to our research review have an impact on perception of prominent colors: color saliency, hue dominance, color coverage, color properties, color diversity, and color span. Within each category, we defined several matrices and used different parameters. To reduce noise in the images and eliminate small variations between neighboring pixels, images were convolved with filter used in S-CIELAB to simulate lower spatial acuity of the HVS,42 before computing the features.

As can be seen from Table 2, three of 10 highly ranked features refer to the color coverage—soft recoloring error, segment soft- and hard-recoloring error. It appears that color coverage is one of the most important factors in the perception of prominent colors. However, all of them include normalization based on the saliency map. This implies that information about color coverage alone is not sufficient and must be adjusted with other factors that are included in saliency models (please note that different saliency models were utilized in these features—Judd and GBVS). In addition, one of these features works on a pixel level, while the other two work on a segment level. It seems that color coverage is important on both dimensions—at lower and higher acuity (resolution).

The next features important for the perception of prominent colors are those concerning a specific color property. The most influential ones are color lightness and chroma. According to our results, the observers on average tend to select colors with a higher mean lightness relative to other colors in the image. In addition, the observers in general selected at least one color with a high chroma value.

This is in line with the analysis of observers' data, which clearly implies that the observers tend to select diverse prominent colors. The weights of both palette diversity features are negative, indicating the negative correlation with the score.

First, during the psychophysical experiment, some observers complained about the limitation of color selection. In particular, some saturated colors were missing or were “washed out.”

It would also be interesting to compare our model with other methods (eg, clustering methods, histogram-based methods) or available solutions (eg, TinEye Lab, Colormind, Canva, Color Thief) for extracting colors from the image.

Only 54%.

http://colormind.io/blog/extracting-colors-from-photos-and-video/ -> Uses a GAN for filtering pallette generation
https://labs.tineye.com/color/925b7924ef2cae34ff1f9c9041c1f5a23e13a99c?ignore_background=True&ignore_interior_background=True&width=92&height=138&scroll_offset=484
https://lokeshdhakar.com/projects/color-thief/

six technically defined dimensions of color appearance: brightness (luminance), lightness, colorfulness, chroma, saturation, and hue.

CIECAM02 (maybe https://github.com/primozw/ui-ciecam02-app/tree/main?)
Brightness is the subjective appearance of how bright an object appears given its surroundings and how it is illuminated. Lightness is the subjective appearance of how light a color appears to be. Colorfulness is the degree of difference between a color and gray. Chroma is the colorfulness relative to the brightness of another color that appears white under similar viewing conditions. This allows for the fact that a surface of a given chroma displays increasing colorfulness as the level of illumination increases. Saturation is the colorfulness of a color relative to its own brightness. Hue is the degree to which a stimulus can be described as similar to or different from stimuli that are described as red, green, blue, and yellow, the so-called unique hues. The colors that make up an object’s appearance are best described in terms of lightness and chroma when talking about the colors that make up the object’s surface, and in terms of brightness, saturation and colorfulness when talking about the light that is emitted by or reflected off the object.

SharpGroteskSmBold-21 for logo font - https://www.sharptype.co/typefaces/sharp-grotesk

https://letterboxd.com/settings/data/ -> Add in a help menu

Truncate table letterboxd.movies

## Home page gallery wall created manually using this code
```
const imgs = document.querySelectorAll('.center img');
let offsetX = 0, offsetY = 0, draggingImg = null;
    imgs.forEach(img => {

    img.addEventListener('mousedown', (e) => {
        e.preventDefault();
        draggingImg = img;
        offsetX = e.clientX - img.offsetLeft;
        offsetY = e.clientY - img.offsetTop;
        img.style.cursor = 'grabbing';
    });

    
});

document.addEventListener('mousemove', (e) => {
    if (!draggingImg) return;
    draggingImg.style.top = `${e.clientY - offsetY}px`;
    draggingImg.style.left = `${e.clientX - offsetX}px`;
});

document.addEventListener('mouseup', () => {
if (draggingImg) {
    draggingImg.style.cursor = 'grab';
    draggingImg = null;
}
});
document.addEventListener('keydown', (e) => {
    if (!draggingImg) return;
    // const step = 10; // Change in size for each keypress
    if (e.key.toLowerCase() === 'w') {
        // Increase size
        // currentWidth += step;
        draggingImg.style.width = `${draggingImg.width + 10}px`;
    } else if (e.key.toLowerCase() === 's') {
        // Decrease size
        // currentWidth = Math.max(step, currentWidth - step); // Prevent size from going below 10px
        draggingImg.style.width = `${draggingImg.width - 10}px`;
    }
});

let total = [];
document.querySelectorAll('.center img').forEach(img => {
  total.push({id: parseInt(img.getAttribute('data-tmdb')), width: img.width, left: parseInt(img.style.left.slice(0, -2)), top: parseInt(img.style.top.slice(0, -2))});
});
```
