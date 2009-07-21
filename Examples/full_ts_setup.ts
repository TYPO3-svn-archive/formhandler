//
//	Project:	formhandler
//	Version:	0.0.2
//	Date:     10.09.2008 
//	Auhor:		Reinhard Führicht <rf@typoheads.at>
// 
// Done with SweeTS - delicious TypoScript development. 
// http://typo3.area42.de
//


/*
**********************************************************    
* Singe step forms
**********************************************************
*/      
plugin.Tx_Formhandler.settings {
  
  /*
  * Path to template file or TS object 
  */
  templateFile = fileadmin/templates/contact.html
  
  /*
  * Path to translation file or TS object  
  */
  langFile = fileadmin/templates/lang/lang_contact.xml
  
  /*
  * Path to stylesheet file  
  */
  stylesheetFile = fileadmin/css/styles_contact.css
  
  /*
  * The view class to use. You can write your own view class.
  * Default: Tx_Formhandler_View_Default  
  */  
  view = Tx_Formhandler_View_Default
  
  /*
  * The controller class to use. You can write your own controller class.
  * Default: Tx_Formhandler_Controller_Default  
  */  
  controller = Tx_Formhandler_Controller_Default
  
  /*
  * Prefix of form fields. Use this if you use a prefix for your forms to avoid
  * conflicts with other plugins. Settings this option you will be able to use
  * only the fieldname in all markers and do not need to add prefix.
  * 
  * NOTE:  It is highly recommended to use this setting!    
  * 
  * Example:      
  *
  * <input type="text" name="formhandler[email]" value="###value_email###" />
  * 
  * If you do not set formValuesPrefix, you will not be able to use the marker 
  * ###value_email###.      
  *
  */      
  formValuesPrefix = formhandler
  
  /*
  * A quite nice little feature.
  * Add markers like ###required_[fieldname]### to your form fields and they
  * will be replaced with this code if the field has the error check "required"
  * in one of the validators.      
  */  
  requiredSign = <div>*</div>
  
  /*
  *
  * Enables debug mode which will output debug messages. Use this to find out 
  * why the form doesn't behave as expected.
  */    
  debug = 1
  
  /*
  *
  * If set, stores the GET / POST variables in the session. May be useful for 
  * later use. 
  */    
  storeGP = 1
  
  /*
  *  Wraps for a single error message.
  *  This settings wrap the messages put into a marker like 
  *  ###error_[fieldname]###.
  *  Since you can do multiple error checks for a single field there may be 
  *  more than one error message.
  */    
  singleErrorTemplate {
    totalWrap = <div>|</div>
    singleWrap = <span class="error">|</span>
  }

  /*
  *  Wraps for the error message list.
  *  This settings wrap the messages put into the marker ###error###.
  *  The marker contains the error messages for all fields.
  */    
  errorListTemplate {
    totalWrap = <div>|</div>
    singleWrap = <span class="error">|</span>
  }

  /*
  *  Experimental:
  *  
  *  This setting replaces all checkboxes and radiobuttons with graphics 
  *  to make them look cooler.    
  *  
  *  Enter the ID of the enclosing form element or any other element to make
  *  the changes only affect this form.    
  */
  fancyForm = 1
  fancyForm.parentId = contact_form
  
  /*
  *  Experimental:
  *  
  *  This setting shows a dropdown list with suggestion words when the user 
  *  types.
  *  Enter a list of fields to add a suggestion box. For each field enter the ID
  *  of the field and a list of words. This list can alos be a TypoScript 
  *  object.
  *    
  */
  autoComplete {
    1 {
      fieldId = firstname
      values = USER
      values.userFunc = user_myClass->user_getSuggestionWords
    }
    2 {
      fieldId = lastname
      values = typo,typoheads,typo3
    }
  }

  /*
  *  Experimental:
  *  
  *  This setting lets help messages appear when the according form field gets 
  *  the focus. The messages fade out on blur.
  *    
  */
  helpTexts {
    
    /*
    * css class of the help messages boxes
    */ 
    className = contexthelp
    
    /*
    * parent id of the form elements
    */ 
    parentId = formhandler_contact_form
    
    /*
    * how many times to add parent() call for traversing in DOM tree to get to a 
    * level to access the help message.
    */ 
    parentTimes = 2
  }

  /*
  *  If you use ###error### and error markers for each field, you can enable 
  *  this settings to add anchor links to each message in ###error###. The 
  *  anchors will point to the messages in ###error_[fieldname]###. 
  *
  */
  addErrorAnchors = 1
  
  /*
  *  Wraps for filenames in a marker like ###[fieldname]_uploadedFiles###.
  *  Since you can upload multiple files via a single field there may be 
  *  more than one filename.
  */    
  singleFileMarkerTemplate {
    totalWrap = <ul>|</ul>
    singleWrap = <li style="color:maroon;">|</li>
  }

  /*
  *  This settings wrap the filenames put into the marker 
  *  ###total_uploadedFiles###.
  *  The marker contains the filenames of all uploaded files.
  */    
  totalFilesMarkerTemplate {
    totalWrap = <ul>|</ul>
    singleWrap = <li style="color:green;">|</li>
  }

  /*
  *  Settings for file uploading.
  */  
  files {
    
    /*
    *  Parses given uploadFolder and deletes every file older than given
    *  hours.
    */  
    clearTempFilesOlderThanHours = 24
    
    /*
    *  Uploaded files will be stored in this folder
    */
    uploadFolder = uploads/formhandler/tmp2/
    
    /*
    *  Adds a remove link to every filename in ###[fieldname]_uploadedFiles### 
    *  and ###total_uploadedFiles###. Unfortunately other markers like 
    *  ###[fieldname]_fileCount### will not be updated at the moment.
    */    
    enableAjaxFileRemoval = 1
  }

  /*
  * Add a list of classes subclassing Tx_Formhandler_AbstractLogger.
  * The loggers will be called just before the finishers. A default
  * implementation of a logger is Tx_Formhandler_Logger_DB.
  * This class logs into tx_formhandler_log.
  */
  loggers {
    1 {
      class = Tx_Formhandler_Logger_DB
    }
  }

  /*
  * Add a list of classes subclassing Tx_Formhandler_AbstractValidator.
  * A default implementation of a validator is Tx_Formhandler_Validator_Default,
  * which provides plenty of error checks. 
  * 
  * If you use an error check which needs a parameter to be set 
  * like "minLength", you can use a marker in your error message to fill in 
  * the configured value of this parameter.
  * 
  * Example:
  * 
  * validators {
  *   1 {
  *     class = Tx_Formhandler_Validator_Default
  *       config {
  *         fieldConf {
  *           firstname {
  *             errorCheck.1 = minLength
  *             errorCheck.1.value = 3    
  *           }
  *           picture {
  *             errorCheck.1 = file_minSize
  *             errorCheck.1.minSize = 20000    
  *           }  
  *         }
  *       }
  *     }
  *   }     
  * }
  *
  * In your translation file you can use
  * 
  * <label index="error_firstname_minLength">
  *   The firstname has to be longer than ###value### characters</label>
  * <label index="error_picture_minSize">
  *   The filesize of picture must be at least ###minSize### bytes.</label>          
  *           
  * For available error checks and parameters have a look into the manual!
  */
  validators {
    1 {
      class = Tx_Formhandler_Validator_Default
      config {
        
        /*
        * Some times you maybe want to disable error checks if the user
        * filled out a specific fields or else.
        * Temporary disable error checking for the entered fields by setting
        * this option.                        
        */        
        disableErrorCheckFields = firstname,lastname
        fieldConf {
          picture {
            errorCheck.1 = file_allowedTypes
            errorCheck.1.allowedTypes = jpg,gif
            errorCheck.2 = file_minSize
            errorCheck.2.minSize = 20000
            errorCheck.3 = file_maxSize
            errorCheck.3.maxSize= 100000
            errorCheck.4 = file_maxCount
            errorCheck.4.maxCount = 2
          }
      	  firstname {
      	    errorCheck.1 = required
      	    errorCheck.2 = maxLength
      	    errorCheck.2.value = 50
      	  }
        }
      }
    }
  }

  /*
  * Add a list of classes subclassing Tx_Formhandler_AbstractInterceptor.
  * The init interceptors will be called each time before the form is processed. 
  * A default implementation of an init interceptor is 
  * Tx_Formhandler_Interceptor_RemoveXSS.
  * This class scans the submitted GET/POST values and removes malicious stuff.
  */
  initInterceptors {
    1 {
      class = Tx_Formhandler_Interceptor_RemoveXSS
    }
  }
	
	/*
  * Add a list of classes subclassing Tx_Formhandler_AbstractInterceptor.
  * The save interceptors will be called before the finishers are called.
  * Use them to prepare the submitted data for sending via e-mail or saving 
  * them to database.  
  */
	saveInterceptors {
    1 {
      class = Tx_Formhandler_Interceptor_Default
    }
  }

  /*
  * Add a list of classes subclassing Tx_Formhandler_AbstractPreProcessor.
  * The pre processors will be called only when the form is shown the first
  * time.
  * Use them to prepare data for prefilling form fields with default values.  
  */
  preProcessors {
    1 {
      class = Tx_Formhandler_PreProcessor_Default
      config {
        
      }
    }
  }

  /*
  * Add a list of classes subclassing Tx_Formhandler_AbstractFinisher.
  * The finishers will be called when the form is successfully submitted.
  * 
  * There are some default implementations of a finisher:
  * 
  * - Tx_Formhandler_Finisher_DB
  * - Tx_Formhandler_Finisher_DifferentDB  
  * - Tx_Formhandler_Finisher_Mail
  * - Tx_Formhandler_Finisher_Redirect
  * - Tx_Formhandler_Finisher_StoreUploadedFiles
  * - Tx_Formhandler_Finisher_ClearCache 
  * - Tx_Formhandler_Finisher_Confirmation                  
  */
  finishers {
     1 {
      
      /*
      * Stores submited values in a table in TYPO3_DB according to the 
      * configured mapping.         
      */
      class = Tx_Formhandler_Finisher_DB
      config {
        
        /*
        * The table to store the values in         
        */
        table = tt_content
        
        /*
        * The uid field. Default: uid      
        */
        key = uid
        
        /*
        * Update an existing record instead of inserting a new one.
        * The uid of the record to update has to be passed along via GET/POST.
        * Not tested yet!
        */        
        updateInsteadOfInsert = 1
        
        /*
        * Mapping settings:
        * 
        * fields.[dbField].mapping = [formField]
        *                                 
        */
        fields {
          header {
            mapping = name
            
            /*
            * No value was submitted for this form field, add this one.
            * Can be String or TS object.            
            */
            ifIsEmpty = None given
          }
          bodytext {
            mapping = interests
            
            /*
            * The value of this form field is an array (e.g. checkboxes). Use 
            * this character to implode the values before storing them.
            * Default: ,                                    
            */
            seperator = ,
          }
        
          /*
          * It is also possible to add static values for some db fields.          
          */
          hidden = 1
          pid = 39
          
          /*
          * Special options for storing submission date as DATETIME or UNIX 
          * timestamp and to store the IP address.
          */
          subheader.special = sub_datetime
          crdate.special = sub_tstamp
          tstamp.special = sub_tstamp
          imagecaption.special = ip
        }
      }
    }
    2 {
      
      /*
      * Sends two different types of e-mails. You can configure an e-mail for
      * admins and for the user who submitted the form.
      * 
      * Specify subparts for plain text and/or HTML mail in your HTML template 
      * file:
      * 
      * <!-- ###TEMPLATE_EMAIL_USER_PLAIN### begin -->
      * Hello ###value_firstname### ###value_lastname###,
      *
      * you have just filled out the form.
      *
      * Thanks for your request, we will answer asap.
      * <!-- ###TEMPLATE_EMAIL_USER_PLAIN### end -->
      *
      * <!-- ###TEMPLATE_EMAIL_USER_HTML### begin -->
      *  <p>
      *  Hello ###value_firstname### ###value_lastname###,
      *  </p>
      *  <p>
      *  you have just filled out the form.
      *  </p>
      *  <p>
      *  Thanks for your request, we will answer asap.
      *  </p>
      *  Typoheads
      *  
      *  <!-- ###TEMPLATE_EMAIL_USER_HTML### end -->
      *
      * <!-- ###TEMPLATE_EMAIL_ADMIN_PLAIN### begin -->
      * Hello Admin,
      *
      * a user has just filled out the form.
      * <!-- ###TEMPLATE_EMAIL_ADMIN_PLAIN### end -->
      *
      * <!-- ###TEMPLATE_EMAIL_ADMIN_HTML### begin -->
      * Hello Admin (HTML),<br />
      * <p>a user has just filled out the form.</p>
      * <!-- ###TEMPLATE_EMAIL_ADMIN_HTML### end -->                              
      * 
      *             
      */
      class = Tx_Formhandler_Finisher_Mail
      config {
        
        /*
        *  To prevent SPAM exploit of the form, limit the e-mails sent.
        */        
        limitMailsToUser = 5
        
        admin {
          
          /*
          * E-Mail header to set the charset for example. 
          */          
          header = ...
          
          /*
          * E-mail address to send the admin mail to.
          * Each of the following settings can be String, TS object or the 
          * name of a form field.          
          */          
          to_email = rf@typoheads.at
          
          /*
          * Name to be added to the receiver's e-mail address        
          */ 
          to_name = Reinhard Führicht
          
          /*
          * Subject of the e-mail          
          */ 
          subject = SingleStep Request
          
          /*
          * E-mail address of the sender.
          * In this case this is the name of the form field where the user 
          * typed his e-mail address into.                             
          */ 
          sender_email = email
          
          /*
          * Name to be added to the sender's e-mail address        
          */
          sender_name = lastname
          
          /*
          * Reply to settings        
          */
          replyto_email = email
          replyto_name = lastname
          
          /*
          * Send only plain text mails but adds the HTMl mail as attachment        
          */
          htmlEmailAsAttachment = 1
          
          /*
          * Attaches static files or files uploaded via form fields to the mail.        
          */
          attachment = picture
          
          /*
          * Attaches a PDF file including the submitted values.
          * A default implementation for a generator is 
          * Tx_Formhandler_Generator_PDF.
          * Feel free to wirte your own generators and templates.                          
          */
          attachPDF.class = Tx_Formhandler_Generator_PDF
          
          /*
          * Use this so that fields like submitted=1 get not exported to the 
          * PDf file 
          */          
          attachPDF.exportFields = firstname,lastname,email,interests
        }
      
        /*
        * Same settings as above
        */        
        user {
          #header = ...
          to_email = email
          to_name = lastname
          subject = Your SingleStep request
          sender_email = rf@typoheads.at
          sender_name = Reinhard Führicht
          replyto_email = rf@typoheads.at
          replyto_name = TEXT
          replyto_name.value = Reinhard Führicht
          htmlEmailAsAttachment = 1
          attachment = picture
          attachPDF.class = Tx_Formhandler_Generator_PDF
          attachPDF.exportFields = firstname,lastname,email,interests
        }
      }
    }
    3 {
      class = Tx_Formhandler_Finisher_DifferentDB
      config {
        host = 10.50.50.60
        port = 666
        db = typoh_3421
        username = typoh_3421
        password = 78c2c55ee8
        table = tt_content
        driver = mysql
        
        /*
        * The rest of the config equals the config of Tx_Formhandler_Finisher_DB
        */            
      }
    }
    4 {
      
       /*
      * Move files from temp folder to another one, if the form was submitted 
      * successfully.
      * The files in temp folder can be deleted by setting 
      * "clearTempFilesOlderThanHours".
      * Use this to store only those files which were uploaded while a 
      * successful form submission and delete files uploaded while an 
      * interrupted form process.            
      */      
      class = Tx_Formhandler_Finisher_StoreUploadedFiles
      config {
        finishedUploadFolder = fileadmin/savedFiles
      }
    }
    5 {
      
      /*
      * Clears cache for this page. Needs no further configuration
      */      
      class = Tx_Formhandler_Finisher_ClearCache
    }
    6 {
      
      /*
      * Redirects to a page or URL.
      * Configure the redirect by entering a page id or full URL.
      * No other finisher is called after this one, because it redirects.
      * Make sure this is the last one in the chain.                  
      */ 
      class = Tx_Formhandler_Finisher_Redirect
      config {
        redirectPage = 23
        
        /*
				* Replaces &amp; with & in URL
				*/
        correctRedirectUrl = 1
      }
    }
    7 {
      
      /*
      * Makes it possible to show an overview of submitted values after the 
      * form was successfully submitted.
      * You can add some links to export the values as PDF or CSV or add a 
      * print link.
      * 
      * If the user reloads the page, the form doesn't get submitted again, only
      * this finisher is called.
      * 
      * Add the subpart ###### to your template.
      * 
      * Example:
      * <!-- ###TEMPLATE_CONFIRMATION### begin -->
      * <table>
      *  <tr>
      *     <td>###LLL:firstname###</td>
      *     <td>###value_firstname###</td>
      *   </tr>
      *   <tr>
      *     <td>###LLL:lastname###</td>
      *     <td>###value_lastname###</td>
      *   </tr>
      *   <tr>
      *     <td>###LLL:email###</td>
      *     <td>###value_email###</td>
      *   </tr>
      * </table>
      * <div>
      * ###PRINT_LINK### / ###PDF_LINK### / ###CSV_LINK###
      * </div>
      * <!-- ###TEMPLATE_CONFIRMATION### end -->                                                                
      */
      class = Tx_Formhandler_Finisher_Confirmation
      config {
      
        /*
        * Since this finishers returns HTML code to show, you have to add this
        * setting. No other finisher will be called after this one, so make sure 
        * that this is the last one in chain.      
        */      
        returns = 1
        
        /*
        * Config for PDF export
        */        
        pdf {
          class = Tx_Formhandler_Generator_PDF
          exportFields = firstname,lastname,interests,pid,ip,submission_date
          
          /*
          * If set the PDF gets rendered to a file, if not the PDf gets 
          * rendered directly to screen
          */          
          export2File = 1
        }
        csv {
          class = Tx_Formhandler_Generator_CSV
          exportFields = firstname,lastname,interests
        }
      }
    }
    
  }
}


/*
**********************************************************    
* Predefined forms
**********************************************************
*/ 
plugin.Tx_Formhandler.settings.predef.contactform < plugin.Tx_Formhandler.settings
plugin.Tx_Formhandler.settings.predef.contactform.name = Contact Form

/*
**********************************************************    
* Multistep forms
**********************************************************
*
* Overwrite settings made before for a specific step.
*/ 
plugin.Tx_Formhandler.settings.1 {
  validators.1.config.fieldConf {
    
  }

  /*
  * Since the handling of checkboxes and radiobuttons is not easy in 
  * multistep forms if you want to check them again if the user goes to next
  * or previous steps, you have to enter which fields in a step are checkboxes
  * or radiobuttons. The controller will take care of the rest.        
  */  
  checkBoxFields = interests
  radionButtonFields = contact_via
}

plugin.Tx_Formhandler.settings.2 {
  validators.1.config.fieldConf {
    
  }
}

/*
**********************************************************    
* Multistep forms with conditions
**********************************************************
*
* Set different settings and template subparts according to user input.
*/ 
[globalVar = GP:formhandler|surname=typoheads]
plugin.Tx_Formhandler.settings.2 {
  templateSuffix = _typoheads
}
[global]

/*
**********************************************************    
* Frontend listing
**********************************************************
*
* List submitted data in frontend with detail view and possibility to delete
* records.
* 
*/

/*
* This is needed to call the right dispatcher, but maybe it's better to put 
* this into a static template file.
*/
includeLibs.Tx_Formhandler_FEListing = EXT:formhandler/Classes/Controller/tx_Formhandler_Dispatcher.php
plugin.Tx_Formhandler_FEListing = USER_INT
plugin.Tx_Formhandler_FEListing.userFunc = tx_Formhandler_Dispatcher->main
tt_content.list.20.formhandler_pi2 < plugin.Tx_Formhandler_FEListing
tt_content.list.20.formhandler_pi2.controller = Tx_Formhandler_Controller_Listing


plugin.Tx_Formhandler.settings.fe_listing {
  view = Tx_Formhandler_View_Listing
  templateFile = fileadmin/templates/ext/formhandler/listing.html
  pid = 39
  table = tt_content
  orderby = subheader DESC
  
  /*
  * Adds links to remove a record from database
  */
  enableDelete = 1
  
  /*
  * Map db fields to fieldnames to use in markers.
  * 
  * Example:
  * 
  * mapping.header = name
  * 
  * Markers in Template:
  * 
  * ###value_name###                
  */
  mapping {
    header = name
    bodytext = subject
    subheader = sub_datetime
    crdate = sub_tstamp
    tstamp = sub_tstamp
    imagecaption = ip
  }
}
