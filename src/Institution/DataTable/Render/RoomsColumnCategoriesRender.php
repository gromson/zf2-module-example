<?php
/**
 * Created by PhpStorm.
 * User: Romson
 * Date: 10.08.2016
 * Time: 16:18
 */

namespace Institution\DataTable\Render;


use DataTable\Data\ColumnInterface;
use DataTable\Render\Column;

class RoomsColumnCategoriesRender extends Column
{
    /**
     * @var array
     */
    protected $categories;

    public function __construct( array $categories, ColumnInterface $dataColumn = null )
    {
        $this->setCategories( $categories );
        parent::__construct( $dataColumn );
    }

    /**
     * @param array $categories
     *
     * @return RoomsColumnCategoriesRender
     */
    public function setCategories( array $categories ): RoomsColumnCategoriesRender
    {
        $this->categories = $categories;

        return $this;
    }

    public function getFilterHtml()
    {
        if ( !$placeholder = $this->dataColumn->getPlaceholder() ) {
            if ( $this->hasTranslator() ) {
                $placeholder = sprintf(
                    $this->getTranslator()->translate( 'Find by %s', 'default' ),
                    $this->getTranslator()->translate( $this->dataColumn->getTitle(), $this->getTranslatorTextDomain() )
                );
            } else {
                $placeholder = sprintf( 'Find by %s', $this->dataColumn->getTitle() );
            }
        } else {
            if ( $this->hasTranslator() ) {
                $placeholder = $this->getTranslator()->translate( $placeholder, $this->getTranslatorTextDomain() );
            }
        }

        $options = '<option value="">- ' . $placeholder . ' -</option>';

        foreach ( $this->categories as $key => $value ) {
            $options .= '<option value="' . $key . '"'
                . ( $this->dataColumn->getFilterValue() == $key ? ' selected="selected"' : '' ) . '>'
                . $value . '</option>';
        }

        return '<select name="' . $this->dataColumn->getName() . '" class="form-control">' . $options . '</select>';
    }
}