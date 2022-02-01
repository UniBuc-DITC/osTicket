<?php
// The code isn't properly localized, it only supports Romanian and English.
$isRomanian = (Internationalization::getCurrentLanguage() == 'ro');
?>

<style>

    /* FOR BUBBLE INFO */

    /* find these in scp/css/scp.css */

    .faded {
        color: #666;
        color: rgba(0,0,0,0.5);
    }
    .faded b {
        color: #333;
        color: rgba(0,0,0,0.75);
    }
    .faded strong {
        color: #444;
        color: rgba(0,0,0,0.6);
    }
    .faded-more {
        color: #aaa;
        color: rgba(0,0,0,0.35);
    }


    i.help-tip {
        vertical-align: inherit;
        color: #aaa;
        opacity: 0.8;
        text-indent: initial;
    }

    i.help-tip:hover {
        color: orange !important;
        cursor: pointer;
        opacity: 1;
    }

    caption > i.help-tip {
        color: white;
        opacity: 0.2;
    }
    caption:hover > i.help-tip {
        color: orange;
        color: #ffc20f;
        opacity: 1;
    }

    h2 > i.help-tip {
        vertical-align: middle;
        font-size: .7em;
    }
    .form_table th h4 i.help-tip {
        color: white;
    }

    tr:hover i.help-tip,
    tr i.help-tip.warning {
        opacity: 0.8 !important;
        color: #ffc20f;
    }

    .form_table tr i.help-tip {
        opacity: 0.2;
        margin-left: 5px;
    }


    .tip_box {
        display:block;
        height:30px;
        position:absolute;
        z-index:10;
    }


    .tip_arrow {
        display:block;
        position:absolute;
        top:5px;
        left:-12px;
        width:12px;
        z-index:1;
    }

    .tip_box.right .tip_arrow {
        top: 5px;
        right: -12px;
        left: auto;
    }

    .flip-x {
        -moz-transform: scaleX(-1);
        -o-transform: scaleX(-1);
        -webkit-transform: scaleX(-1);
        transform: scaleX(-1);
        filter: FlipH;
        -ms-filter: "FlipH";
    }


    .tip_content {
        height:auto !important;
        height:20px;
        min-height:20px;
        padding:10px;
        border:1px solid #666;
        background:#fff;
        -moz-border-radius:5px;
        -webkit-border-radius:5px;
        border-radius:5px;
        -moz-box-shadow: 5px 5px 10px -2px rgba(0,0,0,0.5);
        -webkit-box-shadow: 5px 5px 10px -2px rgba(0,0,0,0.5);
        box-shadow: 5px 5px 10px -2px rgba(0,0,0,0.5);
        z-index:3;
        position:absolute;
        top:0;
        left:-1px;
        min-width:400px;
        line-height: 1.45rem;
    }

    .tip_content .links {
        margin-top: 0.7em;
        padding-top: 0.4em;
        border-top: 1px solid #ddd;
    }

    .tip_content .links a {
        color: #548dd4;
    }

    .tip_content hr {

        color: #ddd;
        background-color: #ddd;
        height: 1px;
        border: 0;
        padding: 0;
        margin: 0.2em 0;
        width: 100%;
    }

    .tip_close {
        position:absolute;
        top:0.3em;
        right:0.5em;
        text-decoration:none;
    }

    .tip_shadow {
        display:none;
        background:#000;
        filter: progid:DXImageTransform.Microsoft.Blur(PixelRadius=3,MakeShadow=true,ShadowOpacity=0.60);
        -ms-filter: "progid:DXImageTransform.Microsoft.Blur(PixelRadius=3,MakeShadow=true,ShadowOpacity=0.60)";
        zoom: 1;
        position:absolute;
        z-index:200;
        top:0;
        left:0;
        width:auto !important;
        width:310px;
    }

    .tip_box th {
        text-align: left;
    }

    .tip_content form {
        display:none;
        line-height:24px;
    }

    .tip_content select, .tip_content textarea {
        width:295px;
    }

    .tip_content textarea {
        padding:0;
        border:1px solid #aaa;
        background:#fff;
    }

    .tip_content form p {
        margin:0;
        width:auto !important;
        width:295px;
        text-align:right;
        line-height:1.5em;
    }

    .tip_content h1 {
        font-size: 1.3em;
        margin-top: 0;
        margin-bottom: 0.4em;
        padding-bottom: 0.5em;
        border-bottom: 1px solid #ddd;
        padding-right: 1.5em;
    }

</style>



<script>

    /** FOR GETTING THE TOPICS */

    const INITIAL_HELP_TOPIC_INFO = {
        'title': "<?php echo $isRomanian ? 'Selecție subiect de ajutor' : 'Select help topic' ?>",
        'content': "<?php echo $isRomanian ?
            'Selectează un subiect de ajutor pentru a afla mai multe informații' :
            'Select a help topic to get more information' ?>",
        'id': -1,
    };

    // pentru afisarea informatiei in bubble info despre topic
    let selectedTopicInfo = { ...INITIAL_HELP_TOPIC_INFO };

    // Contorizeaza nivelul maxim la care utilizatorul a ajuns cu selectia departamentelor.
    let MAX_GENERATED_FIELDS_LEVEL = 0;

    // Dictionar cu id-urile formelor corespunzatoare meniurilor de selectie a (sub)departamentelor.
    generatedForms = [];

    let foundError = false;

    // Id-ul optiunii de selectie vida. Este utila pentru regenerarea meniurilor de selectie de la un anumit nivel in jos,
    // in cazul in care utilizator reselecteaza un (sub)departament deja ales.
    const MENU_EMPTY_SELECT_OPTION = -2;

    // *CONVENTIE*
    // Indica faptul ca nu s-au generat meniuri cu nivele din ierarhia de departamente pana acum.
    const NO_GENERATED_FIELDS = -1;


    /** FOR BUBBLE INFO */
    function setTopicIdInfo(topicId){

        if(topicId <= 0){
            selectedTopicInfo = { ...INITIAL_HELP_TOPIC_INFO };
            return;
        }

        const link = "api/http.php/tickets/helpTopicInfo?topicId=" + topicId;

        $.getJSON(link, function (result) {

            $.each(result, function (fieldName, fieldInfo) {
                if (fieldName === 'topic_info') {
                    selectedTopicInfo.title = ' ' + fieldInfo['topic']; // spatiul este adaugat ca sa nu fie lipit titlul de imaginea cu Info
                    selectedTopicInfo.content = fieldInfo['notes'];
                    selectedTopicInfo.id = fieldInfo['topic_id'];
                }
            });

        });
    }


    /**
     * Declanseaza un apel AJAX pentru a obtine codul HTML
     * pentru formele dinamice corespunzatoare fiecarui help topic.
     *
     * Cod preluat din codebase-ul osTicket.
     */
    function functiaInitialaHelpTopicuri(topicId) {

        setTopicIdInfo(topicId);

        if(parseInt(topicId) === MENU_EMPTY_SELECT_OPTION){
            document.getElementById('dynamic-form').innerHTML = "";
            return;
        }

        var data = $(':input[name]', '#dynamic-form').serialize();
        $.ajax(
            'ajax.php/form/help-topic/' + topicId,
            {
                data: data,
                dataType: 'json',
                xhrFields: {
                    withCredentials: true
                },
                error: function (msg) {
                    alert('Error while initializing help topics');
                },
                success: function (json) {
                    $('#dynamic-form').empty().append(json.html);
                    $(document.head).append(json.media);

                }
            });
    }

    /**
     * Functie utilitara folosita pentru generarea dinamica a optiunilor
     * pentru alegerea (sub)departamentelor.
     */
    function generateTableRows(selectFieldId, selectFieldName, titleRowId, tableRowId, rowName, functionName,
                               selectMessage, isHelpTopicField, selectedValue = null) {

        // First row
        // Titlul meniului de selectie
        let mk = '<tr id="' + titleRowId + '">';
        mk += '<td colspan="2"> <hr/>  <div class="form-header" style="margin-bottom:0.5em">';

        if (isHelpTopicField) {
            mk += '<h1><b>' + rowName + '</b> <span class="error">*</span></h1>';
        }
        else{
            mk += '<b>' + rowName + '</b> <span class="error">*</span>';
        }

        mk += '</div> </td> </tr> ';

        // Second row
        // Meniul de selectie
        mk += '<tr id="' + tableRowId + '">  <td colspan="2">';
        mk += '<select id="' + selectFieldId + '" name="' + selectFieldName + '" onchange="' + functionName + '">';

        if(selectedValue == null)
            mk += '<option value="' + MENU_EMPTY_SELECT_OPTION + '" selected="selected">-- ' + selectMessage + ' --</option>';
        else {
            mk += '<option value="' + MENU_EMPTY_SELECT_OPTION + '">-- ' + selectMessage + ' --</option>';
        }

        mk = mk + '</select>';

        if (isHelpTopicField === true) {
            mk = mk + '  <i class="help-tip icon-question-sign"></i>';
        }

        // All dropdowns are mandatory
        <?php
        $requiredFieldMessage = $isRomanian ? 'este un camp obligatoriu' : 'is required';
        if ($errors) {
        ?>
            if (!foundError && (selectedValue == null || selectedValue < 0)) {
                mk = mk + ' <div class="error">' + rowName + ' <?php echo $requiredFieldMessage ?></div>'
                foundError = true;
            }
        <?php
        }
        ?>

        mk = mk + '</td> </tr>';

        //Adauga meniul
        $("#tabel_topicuri").append(mk);

    }

    /**
     * Sterge toate formele folosite pentru selectarea (sub)departamenelor
     * de la nivelul currentFieldNumber(inclusiv) in jos.
     */
    function deleteAllForms(currentFieldNumber) {

        for (let i = 0; i < generatedForms.length; i++) {
            //Se sterge si nivelul curent deoarece va fi regenerat mai tarziu.
            if (i >= parseInt(currentFieldNumber)) {
                $('#' + generatedForms[i].formTitleRowId).remove();
                $('#' + generatedForms[i].formTableRowId).remove();
            }

            // *Optimizare*
            // Oprire parcurgere daca s-a depasit numarul maxim de nivele generat.
            if (i > MAX_GENERATED_FIELDS_LEVEL) {
                return;
            }

        }

    }

    /**
     * Adauga un nou camp de selectie pentru urmatorul nivel de subdepartamente (currentFieldNumber + 1).
     * Se poate asocia cu inaintarea in ierarhia departamentelor.
     * Se poate folosi parentDepartmentId = -1 pentru a genera departamentele radacina (cele mai sus in ierarhie).
     */
    function addChildField(parentDepartmentId, currentFieldNumber, selectedValue = null) {

        // Se selecteaza optiunea implicita din meniu => Resetarea meniurilor de la nivelul curent in jos
        if (parseInt(parentDepartmentId) === MENU_EMPTY_SELECT_OPTION) {
            deleteAllForms(currentFieldNumber);
            hideTopics();
            return;
        }

        // API request pentru returnarea subdepartamentelor asociate unui departament.
        const API_link = "api/http.php/tickets/publicChildDepartments?departmentId=" + parentDepartmentId;

        // Informatii pentru codul HTML
        const titleRowId = '_' + currentFieldNumber + '_tr_1';
        const tableRowId = '_' + currentFieldNumber + '_tr_2';
        const selectFieldId = '_' + currentFieldNumber + '_select_id';
        const selectFieldName = '_' + currentFieldNumber + '_select_name';

        let exit = false;

        $.ajax({
            type: "GET",
            async: false,
            url: API_link,
            contentType: "application/json",
            dataType: "json",
            xhrFields: {
                withCredentials: true
            },
            error: function (err) {
                console.error(err);
                alert('Error while loading subdepartments for department ' + parentDepartmentId + ': ' + err.responseText)
            },
            success: function (result) {

                // Parsare JSON
                $.each(result, function (fieldName, fieldInfo) {

                    // verifica daca are sub-departamente
                    if (fieldName === 'parent_info' && fieldInfo['finalChild'] === true) {
                        deleteAllForms(currentFieldNumber);

                        if (parseInt(fieldInfo['id']) > 0) {
                            showTopics(fieldInfo['id']);
                        }

                        exit = true;
                    }

                    if (!exit) {

                        // Primul entry din JSON. Contine informatia pentru sub-departamente
                        if (fieldName === 'sub_departments') {

                            hideTopics();
                            exit = false;

                            deleteAllForms(currentFieldNumber);

                            const functionName = 'addChildField(this.value,' + (currentFieldNumber + 1) + ');';

                            if (currentFieldNumber === 0) {
                                generateTableRows(selectFieldId, selectFieldName, titleRowId, tableRowId,
                                    "<?php echo $isRomanian ? 'Departamente' : 'Departments' ?>",
                                    functionName,
                                    "<?php echo $isRomanian ? 'Selectează un departament' : 'Select a Department' ?>",
                                    false,
                                    selectedValue);
                            } else {
                                generateTableRows(selectFieldId, selectFieldName, titleRowId, tableRowId,
                                    "<?php echo $isRomanian ? 'Sub-departamente' : 'Departamente' ?>",
                                    functionName,
                                    "<?php echo $isRomanian ? 'Selectează un sub-departament' : 'Select a Sub-department' ?>",
                                    false,
                                    selectedValue);
                            }

                            MAX_GENERATED_FIELDS_LEVEL = currentFieldNumber;

                            // Salvare camp generat
                            if (MAX_GENERATED_FIELDS_LEVEL >= generatedForms.length) {
                                generatedForms[MAX_GENERATED_FIELDS_LEVEL] = Object();
                                generatedForms[MAX_GENERATED_FIELDS_LEVEL].formTitleRowId = titleRowId;
                                generatedForms[MAX_GENERATED_FIELDS_LEVEL].formTableRowId = tableRowId;
                            }

                            // add options
                            $.each(fieldInfo, function (i, field) {
                                if (parseInt(field['id']) === parseInt(selectedValue)) {
                                    $('#' + selectFieldId).append($('<option>', {
                                        value: field['id'],
                                        text: field['name'],
                                        selected: 'selected'
                                    }));
                                } else {
                                    $('#' + selectFieldId).append($('<option>', {
                                        value: field['id'],
                                        text: field['name']
                                    }));
                                }
                            });
                        }

                    }


                });
            }
        });

    }

    /**
     * Afiseaza help topicurile corespunzatoare selectiei de departamente anterioare.
     * In afisare se vor include si toate help topicurile departamentelor indiferent de nivelul din ierarhie
     * Exemplu:
     * Selectia Facultati -> FMI -> Informatica
     * va genera help topicurile specifice Facultatilor + help topicurile specifice facultatii FMI +
     * help topicurile specifice specializarii Informatica.
     *
     * Toata aceasta logica de aflare a help topicurilor corespunzatoare este tratata de catre API.
     */
    function showTopics(departmentId, selectedValue = null) {

        if (parseInt(departmentId) <= 0) {
            console.error(departmentId + " < 0");
            return;
        }

        hideTopics();

        let trFieldId_1 = "topicId_tr_1";
        let trFieldId_2 = "topicId_tr_2";
        const selectFieldId = "topicId";
        const selectFieldName = "topicId";

        const link = "api/http.php/tickets/departmentHelpTopics?departmentId=" + departmentId + "&&includeParentsHelpTopics=true";

        generateTableRows(selectFieldId, selectFieldName, trFieldId_1, trFieldId_2,
            "<?php echo $isRomanian ? 'Subiecte de ajutor' : 'Help topics' ?>",
            "functiaInitialaHelpTopicuri(this.value);",
            "<?php echo $isRomanian ? 'Selectează un subiect' : 'Select a help topic' ?>",
            true, selectedValue);

        $.ajax({
            type: "GET",
            async: false,
            url: link,
            contentType: "application/json",
            dataType: "json",
            xhrFields: {
                withCredentials: true
            },
            error: function (err) {
                console.error(err);
                alert('Error while retrieving help topics for department ' + departmentId + ': ' + err.statusText)
            },
            success: function (result) {

                $.each(result, function (fieldName, fieldInfo) {

                    if (fieldName === 'help_topics') {

                        $.each(fieldInfo, function (i, field) {
                            if(parseInt(field['id']) === parseInt(selectedValue)){
                                $('#' + selectFieldId).append($('<option>', {
                                    value: field['id'],
                                    text: field['name'],
                                    selected: 'selected'
                                }));
                            }
                            else{
                                $('#' + selectFieldId).append($('<option>', {
                                    value: field['id'],
                                    text: field['name']
                                }));
                            }
                        });
                    }
                });
            }
        });
    }

    /**
     * Functie utilitara care ascunde help topicurile generate de selectia departamentelor.
     */
    function hideTopics() {
        if(document.getElementById('dynamic-form') != null){
            document.getElementById('dynamic-form').innerHTML = "";

            $('#topicId_tr_1').remove();
            $('#topicId_tr_2').remove();
        }
    }


    /** FOR BUBBLE INFO */
    jQuery(function() {

        $(document)
            .on('mouseover click', '.help-tip', function(e) {
                e.preventDefault();

                var elem = $(this),
                    pos = elem.offset(),
                    y_pos = pos.top - 8,
                    x_pos = pos.left + elem.width() + 16,
                    tip_arrow = $('<img>')
                        .attr('src', './images/tip_arrow.png')
                        .addClass('tip_arrow'),
                    tip_box = $('<div>')
                        .addClass('tip_box'),
                    tip_content = $('<div>')
                        .append('<a href="#" class="tip_close"><i class="icon-remove-circle"></i></a>')
                        .addClass('tip_content'),
                    the_tip = tip_box
                        .append(tip_content.append(tip_arrow))
                        .css({
                            "top":y_pos + "px",
                            "left":x_pos + "px"
                        }),
                    tip_timer = setTimeout(function() {
                        $('.tip_box').remove();
                        $('body').append(the_tip.hide().fadeIn());
                        var width = $(window).width(),
                            rtl = $('html').hasClass('rtl'),
                            size = tip_content.outerWidth(),
                            left = the_tip.position().left,
                            left_room = left - size,
                            right_room = width - size - left,
                            flip = rtl
                                ? (left_room > 0 && left_room > right_room)
                                : (right_room < 0 && left_room > right_room);
                        if (flip) {
                            the_tip.css({'left':x_pos-tip_content.outerWidth()-40+'px'});
                            tip_box.addClass('right');
                            tip_arrow.addClass('flip-x');
                        }
                    }, 500);

                tip_content.append(
                    $('<h1>')
                        .append('<i class="icon-info-sign faded">')
                        .append(' ' + selectedTopicInfo['title'])
                    ).append(selectedTopicInfo['content']);

                elem.on('mouseout', function() {
                    clearTimeout(tip_timer);
                });

                $('.tip_shadow', the_tip).css({
                    "height":the_tip.height() + 5
                });
            });


        $('body')
            .delegate('.tip_close', 'click', function(e) {
                e.preventDefault();
                $(this).parent().parent().remove();
            });

        $(document).on('mouseup', function (e) {
            var container = $('.tip_box');
            if (!container.is(e.target)
                && container.has(e.target).length === 0) {
                container.remove();
            }
        });
    });

</script>


<?php

include_once INCLUDE_DIR . 'api.tickets.php';

if (!defined('OSTCLIENTINC')) die('Access Denied!');
$info = array();
if ($thisclient && $thisclient->isValid()) {
    $info = array('name' => $thisclient->getName(),
        'email' => $thisclient->getEmail(),
        'phone' => $thisclient->getPhoneNumber());
}

$info=($_POST && $errors)?Format::htmlchars($_POST):$info;

$form = null;
if (!$info['topicId']) {
    if (array_key_exists('topicId',$_GET) && preg_match('/^\d+$/',$_GET['topicId']) && Topic::lookup($_GET['topicId']))
        $info['topicId'] = intval($_GET['topicId']);
    else
        $info['topicId'] = $cfg->getDefaultTopicId();
}

$forms = array();
if ($info['topicId'] && ($topic=Topic::lookup($info['topicId']))) {
    foreach ($topic->getForms() as $F) {
        if (!$F->hasAnyVisibleFields())
            continue;
        if ($_POST) {
            $F = $F->instanciate();
            $F->isValidForClient();
        }
        $forms[] = $F->getForm();
    }
}

?>
<h1><?php echo __('Open a New Ticket');?></h1>
<p><?php echo __('Please fill in the form below to open a new ticket.');?></p>
<form id="ticketForm" method="post" action="open.php" enctype="multipart/form-data">
    <?php csrf_token(); ?>
    <input type="hidden" name="a" value="open">
    <table width="800" cellpadding="1" cellspacing="0" border="0">
        <!-- INFO: Forma cu datele de contact. Nu este necesara deoarece se tine cont de conectarea cu MS -->
        <tbody>
        <?php
        if (!$thisclient) {
            $uform = UserForm::getUserForm()->getForm($_POST);
            if ($_POST) $uform->isValid();
            $uform->render(array('staff' => false, 'mode' => 'create'));
        }
        else { ?>
            <tr>
                <td colspan="2">
                    <hr/>
                </td>
            </tr>
            <tr>
                <td><?php echo __('Email'); ?>:</td>
                <td><?php
                    echo $thisclient->getEmail(); ?></td>
            </tr>
            <tr>
                <td><?php echo __('Client'); ?>:</td>
                <td><?php
                    echo Format::htmlchars($thisclient->getName()); ?></td>
            </tr>
        <?php } ?>
        </tbody>

        <!--        INFO: Varianta 1 de afisat departamentul radacina (dinamic) -->
        <!--        Se specifica faptul ca nu s-a generat niciun meniu de selectie-->

        <tbody id="tabel_topicuri">
        <?php
        if($_POST)
        {
            //Reconstruire array de subtopicuri

            $matches = preg_grep('/_select_name/', array_keys($_POST));

            $subtopicuri = array();
            foreach ($matches as $index => $val) {
                array_push($subtopicuri, $_POST[$val]);
            }

            $last_val = 0;

            echo '<script>';
            foreach ($subtopicuri as $id => $val) {
                if($id == 0){
                    echo "addChildField(NO_GENERATED_FIELDS, ". $id . "," . $val .");";
                }
                else {
                    echo "addChildField(". $last_val .", ". $id . "," . $val .");";
                }
                $last_val = $val;
            }

            $selectedTopic = is_numeric($_POST['topicId']) ? $_POST['topicId'] : 'null';
            $js = 'showTopics(' . $last_val . ',' . $selectedTopic . ');';
            echo $js . '</script>';
        }
        else
        {
            echo
            '<script>
                addChildField(NO_GENERATED_FIELDS, 0);
                </script>';
        }
        ?>
        </tbody>
        <tbody id="dynamic-form">
        <?php
        $options = array('mode' => 'create');
        foreach ($forms as $form) {
            include(CLIENTINC_DIR . 'templates/dynamic-form.tmpl.php');

        } ?>
        </tbody>
        <tbody>
        <?php

        if ($cfg && $cfg->isCaptchaEnabled() && (!$thisclient || !$thisclient->isValid())) {
            if ($_POST && $errors && !$errors['captcha'])
                $errors['captcha'] = __('Please re-enter the text again');
            ?>
            <tr class="captchaRow">
                <td class="required"><?php echo __('CAPTCHA Text'); ?>:</td>
                <td>
                    <span class="captcha"><img src="captcha.php" border="0" align="left"></span>
                    &nbsp;&nbsp;
                    <input id="captcha" type="text" name="captcha" size="6" autocomplete="off">
                    <em><?php echo __('Enter the text shown on the image.'); ?></em>
                    <span class="error">*&nbsp;<?php echo $errors['captcha']; ?></span>
                </td>
            </tr>
            <?php

        } ?>
        <tr>
            <td colspan=2>&nbsp;</td>
        </tr>
        </tbody>
    </table>
    <hr/>
    <p class="buttons" style="text-align:center;">
        <input type="submit" value="<?php echo __('Create Ticket'); ?>">
        <input type="reset" name="reset" value="<?php echo __('Reset'); ?>" onclick="javascript:
            deleteAllForms(1);
            hideTopics();
        ">
        <!--        Problema buton cancel. Nu oferim suport pentru el-->
        <!--        <input type="button" name="cancel" value="--><?php //echo __('Cancel'); ?><!--" onclick="javascript:-->
        <!---->
        <!--            $('.richtext').each(function() {-->
        <!--                var redactor = $(this).data('redactor');-->
        <!--                if (redactor && redactor.opts.draftDelete)-->
        <!--                    redactor.draft.deleteDraft();-->
        <!--            });-->
        <!--            window.location.href='index.php';">-->
    </p>
</form>
