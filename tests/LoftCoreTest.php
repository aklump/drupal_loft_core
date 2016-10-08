<?php

class LoftCoreTest extends PHPUnit_Framework_TestCase
{
    /**
     * Provides data for testFormDisableElements.
     */
    function DataForTestFormDisableElementsProvider()
    {
        $tests = array();

        // Test One
        $form = array();
        $form['field_description']['und'][0] = array();
        $form['field_description']['und'][0]['#description'] = "Enter data";

        $control_form = $form;
        $control_form['field_description']['und'][0]['#disabled'] = true;
        $control_form['field_description']['und'][0]['#description'] = "This field is controlled by the Loft Core module and cannot be modified in the UI.";

        $tests[] = array(
            $form,
            array('field_description.und.0'),
            null,
            null,
            $control_form,
        );

        // Test Two
        $form = array();
        $form['field_description']['und'][0] = array();
        $form['field_description']['und'][0]['#description'] = "Enter data";

        $control_form = $form;
        $control_form['field_description']['und'][0]['#disabled'] = true;
        $control_form['field_description']['und'][0]['#description'] = "You can't enter data here, my friend.";

        $tests[] = array(
            $form,
            array('field_description.und.0'),
            'Test Bot',
            'You can\'t enter data here, my friend.',
            $control_form,
        );

        // Test Three
        $form = array();
        $form['field_description']['und'][0] = array();
        $form['field_description']['und'][0]['#description'] = "Enter data";

        $control_form = $form;
        $control_form['field_description']['und'][0]['#disabled'] = true;
        $control_form['field_description']['und'][0]['#description'] = "This field is controlled by the Test Bot module and cannot be modified in the UI.";

        $tests[] = array(
            $form,
            array('field_description.und.0'),
            'Test Bot',
            null,
            $control_form,
        );

        return $tests;
    }

    /**
     * @dataProvider DataForTestFormDisableElementsProvider
     */
    public function testFormDisableElements($form, $paths, $module_name, $message, $control_form)
    {
        loft_core_form_disable_elements($form, $paths, $module_name, $message);
        $this->assertSame($control_form, $form);
    }
}
