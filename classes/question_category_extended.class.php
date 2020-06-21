<?php

require_once($CFG->dirroot.'/question/category_class.php');

class question_category_object_extended extends question_category_object {

    /**
     * Returns html string.
     *
     * @param integer $indent depth of indentation.
     */
    public function to_html($indent = 0, $extraargs = array()) {
        if (count($this->items)) {
            $first = true;
            $itemiter = 1;
            $lastitem = '';
            $html = '';

            foreach ($this->items as $item) {

                $last = (count($this->items) == $itemiter);
                if ($this->editable) {
                    $item->set_icon_html($first, $last, $lastitem);
                }

                if ($itemhtml = $item->to_html($indent + 1, $extraargs)) {
                    $html .= $itemhtml;
                }

                $first = false;
                $lastitem = $item;
                $itemiter++;
            }
        } else {
            $html = '';
        }
        if ($html) { //if there are list items to display then wrap them in ul / ol tag.
            $tabs = str_repeat("\t", $indent);
            $html = $tabs.'<'.$this->type.((!empty($this->attributes))?(' '.$this->attributes):'').">\n".$html;
            $html .= $tabs."</".$this->type.">\n";
        } else {
            $html ='';
        }
        return $html;
    }

    /**
     * Initializes this classes general category-related variables
     */
    public function initialize($page, $contexts, $currentcat, $defaultcategory, $todelete, $addcontexts) {
        $lastlist = null;
        foreach ($contexts as $context){
            $this->editlists[$context->id] = new question_category_list_extended('ul', '', true, $this->pageurl, $page, 'cpage', QUESTION_PAGE_LENGTH, $context);
            $this->editlists[$context->id]->lastlist =& $lastlist;
            if ($lastlist!== null){
                $lastlist->nextlist =& $this->editlists[$context->id];
            }
            $lastlist =& $this->editlists[$context->id];
        }

        $count = 1;
        $paged = false;
        foreach ($this->editlists as $key => $list){
            list($paged, $count) = $this->editlists[$key]->list_from_records($paged, $count);
        }
        $this->catform = new question_category_edit_form($this->pageurl, compact('contexts', 'currentcat'));
        if (!$currentcat){
            $this->catform->set_data(array('parent' => $defaultcategory));
        }
    }
}

class question_category_list_extended extends question_category_list {
    public $listitemclassname = 'question_category_list_item_extended';


    /**
     * Returns html string.
     *
     * @param integer $indent depth of indentation.
     */
    public function to_html($indent=0, $extraargs=array()) {
        if (count($this->items)) {
            $tabs = str_repeat("\t", $indent);
            $first = true;
            $itemiter = 1;
            $lastitem = '';
            $html = '';

            foreach ($this->items as $item) {
                $last = (count($this->items) == $itemiter);
                if ($this->editable) {
                    $item->set_icon_html($first, $last, $lastitem);
                }
                if ($itemhtml = $item->to_html($indent+1, $extraargs)) {
                    $html .= $itemhtml;
                }
                $first = false;
                $lastitem = $item;
                $itemiter++;
            }
        } else {
            $html = '';
        }
        return $html;
    }
}

class question_category_list_item_extended extends question_category_list_item {

    protected $currentindent = 0;

    /**
     * overrides the question_category_list_item item_html()
     */
    public function item_html($extraargs = array()) {
        global $CFG, $OUTPUT;

        $str = $extraargs['str'];
        $category = $this->item;
        $tabs = str_repeat("&nbsp;&nbsp;", $this->currentindent);

        $editqestions = get_string('editquestions', 'question');

        // Each section adds html to be displayed as part of this list item.
        $questionbankurl = new moodle_url('/question/edit.php', $this->parentlist->pageurl->params());
        $questionbankurl->param('cat', $category->id . ',' . $category->contextid);
        $item = '';
        $text = format_string($category->name, true, ['context' => $this->parentlist->context])
                . ' (' . $category->questioncount . ')';
        $classes = ['href' => '#', 'id' => 'question-category-handle-'.$category->id, 'class' => 'question-category-handle', 'aria-expanded' => 'true'];
        $handlelink = html_writer::tag('a', '', $classes);
        $item .= $tabs.$handlelink.'&nbsp;'.html_writer::link($questionbankurl, $text,
                        ['title' => $editqestions]);
        $item .= format_text($category->info, $category->infoformat,
                array('context' => $this->parentlist->context, 'noclean' => true));

        return $item;
    }

    /**
     * overrides the moodle_list_item to_html()
     *
     * @param integer $indent
     * @param array $extraargs any extra data that is needed to print the list item
     *                            may be used by sub class.
     * @return string html
     */
    public function to_html($indent = 0, $extraargs = array()) {
        global $OUTPUT;

        $str = $extraargs['str'];
        $this->currentindent = $indent;

        if (!$this->display) {
            return '';
        }
        $category = $this->item;

        if (isset($this->children) && !empty($this->children->items)) {
            $childrenhtml = $this->children->to_html($indent, $extraargs);
            $hassubs = true;
        } else {
            $childrenhtml = '';
            $hassubs = false;
        }

        $itemhtml = $this->item_html($extraargs);

        // Don't allow delete if this is the top category, or the last editable category in this context.
        $cmds = '';
        if ($category->parent && !question_is_only_child_of_top_category_in_context($category->id)) {
            $deleteurl = new moodle_url($this->parentlist->pageurl, array('delete' => $category->id, 'sesskey' => sesskey()));
            $cmds = html_writer::link($deleteurl,
                    $OUTPUT->pix_icon('t/delete', $str->delete),
                    array('title' => $str->delete));
        }

        $cmds .= '&nbsp;'.(join($this->icons, ''));

        $subs = '';
        if (!empty($childrenhtml)) {
            $subs = ('<div id="question-category-sub-'.$this->id.'" class="question-category sub">'.$childrenhtml.'</div>');
        }

        $isemptyclass = 'is-not-empty';
        if ($category->questioncount == 0 && !$hassubs) {
            $isemptyclass = 'is-empty';
        }

        $str = '
            <div id="question-category-'.$this->id.'" class="question-category level-'.$indent.' '.$isemptyclass.'" '.$this->attributes.'>
                <div class="question-category-header">
                    <div class="question-category-item">
                    '.$itemhtml.'
                    </div>
                    <div class="question-category-commands">
                    '.$cmds.'
                    </div>
                </div>
                '.$subs.'
            </div>
        ';

        return $str;
    }

}