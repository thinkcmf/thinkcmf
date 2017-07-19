<?php
/**
 * @package php-svg-lib
 * @link    http://github.com/PhenX/php-svg-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Svg\Tag;

use Svg\Style;

class Shape extends AbstractTag
{
    protected function before($attributes)
    {
        $surface = $this->document->getSurface();

        $surface->save();

        $style = $this->makeStyle($attributes);

        $this->setStyle($style);
        $surface->setStyle($style);

        $this->applyTransform($attributes);
    }

    protected function after()
    {
        $surface = $this->document->getSurface();

        if ($this->hasShape) {
            $style = $surface->getStyle();

            $fill   = $style->fill   && $style->fill   !== "none";
            $stroke = $style->stroke && $style->stroke !== "none";

            if ($fill) {
                if ($stroke) {
                    $surface->fillStroke();
                } else {
//                    if (is_string($style->fill)) {
//                        /** @var LinearGradient|RadialGradient $gradient */
//                        $gradient = $this->getDocument()->getDef($style->fill);
//
//                        var_dump($gradient->getStops());
//                    }

                    $surface->fill();
                }
            }
            elseif ($stroke) {
                $surface->stroke();
            }
            else {
                $surface->endPath();
            }
        }

        $surface->restore();
    }
} 