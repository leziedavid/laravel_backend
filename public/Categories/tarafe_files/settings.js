
    jQuery( function($) {  
        var Form = function(form) {
            if(typeof form[0] != "undefined"){
                var fields = [];
                // Get all input elements in form
                try{ 
                    $(form[0].elements).each(function(index) {
                        var field = $(this);
                        // We're only interested in fields with a validation attribute

                        if((typeof field.attr('class') != "undefined" && field.attr('class').includes('billing'))
                            || (typeof field.parent().attr('class') != "undefined" && field.parent().attr('class').includes('billing'))
                            || (typeof field.parent().parent().attr('class') != "undefined" && field.parent().parent().attr('class').includes('billing'))
                            || (typeof field.parent().parent().parent().attr('class') != "undefined" && field.parent().parent().parent().attr('class').includes('billing'))                                                
                            || (typeof field.parent().parent().parent().parent().attr('class') != "undefined" && field.parent().parent().parent().parent().attr('class').includes('billing'))) {
                            
                            if((typeof field.attr('class') != "undefined" && field.attr('class').includes('required'))
                                    || (typeof field.parent().attr('class') != "undefined" && field.parent().attr('class').includes('required') )
                                    || (typeof field.parent().parent().attr('class') != "undefined" && field.parent().parent().attr('class').includes('required'))  
                                    || (typeof field.parent().parent().parent().attr('class') != "undefined" && field.parent().parent().parent().attr('class').includes('required'))
                                    || (typeof field.parent().parent().parent().parent().attr('class') != "undefined" && field.parent().parent().parent().parent().attr('class').includes('required'))                                                
                                    || (typeof field.attr('validation') != "undefined" && field.attr('validation'))) {
                                field.attr('validation',index)
                                fields.push(new Field(field));
                            }
                        }
                    });
                    this.fields = fields;
                    //console.log(this.fields)
                }catch(err) {
                    //console.log(err)
                }
            }else{
                //console.log("not already at the good page")
                return;
            }
        }
        
          var Field = function(field) {
            this.field = field;
            this.valid = false;
            this.attach("change");
        }
        
        //Ce sont les deux méthodes attachées à l'objet prototype Field.

        Field.prototype = {
            // Method used to attach different type of events to
            // the field object.
            attach : function(event) {

                var obj = this;
                if(event == "change") {
                    obj.field.bind("change",function() {
                        return obj.validate();
                    });
                }
                if(event == "keyup") {
                    obj.field.bind("keyup",function(e) {
                        return obj.validate();
                    });
                }
            },

            // Method that runs validation on a field
            validate : function() {
                // Create an internal reference to the Field object.
                var obj = this,
                    // The actual input, textarea in the object
                    field = obj.field,
                    // A field can have multiple values to the validation
                    // attribute, seprated by spaces.
                    types = "valid";

                    try{ 
                        if(field[0].value == ""
                            || field.attr('class').includes('invalid')
                            || field.parent().attr('class').includes('invalid') 
                            || field.parent().parent().attr('class').includes('invalid')  
                            || field.parent().parent().parent().attr('class').includes('invalid')
                            || field.parent().parent().parent().parent().attr('class').includes('invalid')){
                            
                            types = "invalid"
                            
                        }
                     }catch(err) {
                        //console.log(err)
                     }
                    errors = []; 

                // If there is an errorlist already present
                // remove it before performing additional validation

                // Iterate over validation types
//                console.log(types)
//                for (var type in types) {

                    // Get the rule from our Validation object.

//                    var rule = $.Validation.getRule(types[type]);
//                    if(!rule.check(field.val())) {
//                    console.log(types)
                    if(types !== "valid") {
                          errors.push("error");
 //                       container.addClass("error");
//                        errors.push(rule.msg);
                    }
//                }
                // If there is one ore more errors
                if(errors.length) {

                    // Remove existing event handler
                    obj.field.unbind("keyup")
                    // Attach the keyup event to the field because now
                    // we want to let the user know as soon as she has
                    // corrected the error
                    obj.attach("keyup");

                    // Empty existing errors, if any.
//                    field.after(errorlist.empty());
                    for(error in errors) {

//                        errorlist.append("<li>"+ errors[error] +"</li>");
                    }
                    obj.valid = false;
                }
                // No errors
                else {
//                    errorlist.remove();
//                    container.removeClass("error");
                    obj.valid = true;
                }
            }
        }
        
        // try {
        //     var comment_form = new Form($("form.woocommerce-checkout"));
        //     console.log(comment_form);
        // } catch (error) {
        //     console.error(error);
        // }
       
        
        
        // fonctionnalités supplémentaires à l'objet Form
        Form.prototype = {
            validate : function() {

                for(field in this.fields) {

                    this.fields[field].validate();
                }
            },
            isValid : function() {
                
                for(field in this.fields) {
                    //console.log(this.fields[field])
                    if(!this.fields[field].valid) {

                        // Focus the first field that contains
                        // an error to let user fix it.
                        this.fields[field].field.focus();

                        // As soon as one field is invalid
                        // we can return false right away.
                        return false;
                    }
                }
                return true;
            }
        }
        
        
        

        //plugin accessible en tant que méthodes sur n'importe quel objet jQuery
        $.extend($.fn,{

            validation : function() {
                if(typeof $(this)[0] != "undefined"){
                    var validator = new Form($(this));
                    $.data($(this)[0], 'validator', validator);

                    $(this).bind("submit", function(event) {
                       
                        validator.validate();
                         
                        if(!validator.isValid()) {
                            e.preventDefault();
                        }
                    });
                }
            },
            validate : function() {
                if(typeof $(this)[0] != "undefined"){
                    var validator = $.data($(this)[0], 'validator');
                    validator.validate();
                    return validator.isValid();
                }
            }
        });

        $( document ).ready(function() {
                var numero_commande = getCookie("txnid");
                if (numero_commande != "") {
                    create_submit_form();
                }
         });
      
    });

    function getCookie(cname) {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for(var i = 0; i <ca.length; i++) {
          var c = ca[i];
          while (c.charAt(0) == ' ') {
            c = c.substring(1);
          }
          if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
          }
        }
        return "";
      }

    function deleteCookie(cname){
        document.cookie = cname+"=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    }
    