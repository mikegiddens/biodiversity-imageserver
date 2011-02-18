/*!
 * Ext JS Library 3.3.1
 * Copyright(c) 2006-2010 Sencha Inc.
 * licensing@sencha.com
 * http://www.sencha.com/license
 */
/**
 * @class Ext.XTemplate
 * @extends Ext.Template
 * 

A template class that supports advanced functionality like:


    *  * Autofilling arrays using templates and sub-templates

    *  * Conditional processing with basic comparison operators

    *  * Basic math function support

    *  * Execute arbitrary inline code with special built-in template variables

    *  * Custom member functions

    *  * Many special tags and built-in operators that aren't defined as part of
       * the API, but are supported in the templates that can be created

       * 


 * 

XTemplate provides the templating mechanism built into:


    *  * {@link Ext.DataView}

    *  * {@link Ext.ListView}

    *  * {@link Ext.form.ComboBox}

    *  * {@link Ext.grid.TemplateColumn}

    *  * {@link Ext.grid.GroupingView}

    *  * {@link Ext.menu.Item}

    *  * {@link Ext.layout.MenuLayout}

    *  * {@link Ext.ColorPalette}

       * 


 *
 * 

For example usage {@link #XTemplate see the constructor}.

 *
 * @constructor
 * The {@link Ext.Template#Template Ext.Template constructor} describes
 * the acceptable parameters to pass to the constructor. The following
 * examples demonstrate all of the supported features.


 *
 * 


       *
    *  * Sample Data
       *

       *

      This is the data object used for reference in each code example:

       *


      var data = {
          name: 'Jack Slocum',
          title: 'Lead Developer',
          company: 'Ext JS, LLC',
          email: 'jack@extjs.com',
          address: '4 Red Bulls Drive',
          city: 'Cleveland',
          state: 'Ohio',
          zip: '44102',
          drinks: ['Red Bull', 'Coffee', 'Water'],
          kids: [{
              name: 'Sara Grace',
              age:3
          },{
              name: 'Zachary',
              age:2
          },{
              name: 'John James',
              age:0
          }]
      };
       * 


       *

       *

       *
       *
    *  * Auto filling of arrays
       *

       *

      The tpl tag and the for operator are used
       * to process the provided data object:
       *

          o  * If the value specified in for is an array, it will auto-fill,
             * repeating the template block inside the tpl tag for each item in the
             * array.

          o  * If for="." is specified, the data object provided is examined.

          o  * While processing an array, the special variable {#}
             * will provide the current array index + 1 (starts at 1, not 0).

             * 
       *


       *


      <tpl for=".">...</tpl>       // loop through array at root node
      <tpl for="foo">...</tpl>     // loop through array at foo node
      <tpl for="foo.bar">...</tpl> // loop through array at foo.bar node
       * 


       * Using the sample data above:
       *


      var tpl = new Ext.XTemplate(
          '<p>Kids: ',
          '<tpl for=".">',       // process the data.kids node
              '<p>{#}. {name}</p>',  // use current array index to autonumber
          '</tpl></p>'
      );
      tpl.overwrite(panel.body, data.kids); // pass the kids property of the data object
       * 


       *

      An example illustrating how the for property can be leveraged
       * to access specified members of the provided data object to populate the template:

       *


      var tpl = new Ext.XTemplate(
          '<p>Name: {name}</p>',
          '<p>Title: {title}</p>',
          '<p>Company: {company}</p>',
          '<p>Kids: ',
          '<tpl for="kids">',     // interrogate the kids property within the data
              '<p>{name}</p>',
          '</tpl></p>'
      );
      tpl.overwrite(panel.body, data);  // pass the root node of the data object
       * 


       *

      Flat arrays that contain values (and not objects) can be auto-rendered
       * using the special {.} variable inside a loop.  This variable
       * will represent the value of the array at the current index:

       *


      var tpl = new Ext.XTemplate(
          '<p>{name}\'s favorite beverages:</p>',
          '<tpl for="drinks">',
             '<div> - {.}</div>',
          '</tpl>'
      );
      tpl.overwrite(panel.body, data);
       * 


       *

      When processing a sub-template, for example while looping through a child array,
       * you can access the parent object's members via the parent object:

       *


      var tpl = new Ext.XTemplate(
          '<p>Name: {name}</p>',
          '<p>Kids: ',
          '<tpl for="kids">',
              '<tpl if="age > 1">',
                  '<p>{name}</p>',
                  '<p>Dad: {parent.name}</p>',
              '</tpl>',
          '</tpl></p>'
      );
      tpl.overwrite(panel.body, data);
       * 


       *

       *

       *
       *
    *  * Conditional processing with basic comparison operators
       *

       *

      The tpl tag and the if operator are used
       * to provide conditional checks for deciding whether or not to render specific
       * parts of the template. Notes:

          o  * Double quotes must be encoded if used within the conditional

          o  * There is no else operator — if needed, two opposite
             * if statements should be used.

             * 

       *


      <tpl if="age > 1 && age < 10">Child</tpl>
      <tpl if="age >= 10 && age < 18">Teenager</tpl>
      <tpl if="this.isGirl(name)">...</tpl>
      <tpl if="id==\'download\'">...</tpl>
      <tpl if="needsIcon"><img src="{icon}" class="{iconCls}"/></tpl>
      // no good:
      <tpl if="name == "Jack"">Hello</tpl>
      // encode " if it is part of the condition, e.g.
      <tpl if="name == &quot;Jack&quot;">Hello</tpl>
       * 


       * Using the sample data above:
       *


      var tpl = new Ext.XTemplate(
          '<p>Name: {name}</p>',
          '<p>Kids: ',
          '<tpl for="kids">',
              '<tpl if="age > 1">',
                  '<p>{name}</p>',
              '</tpl>',
          '</tpl></p>'
      );
      tpl.overwrite(panel.body, data);
       * 


       *

       *

       *
       *
    *  * Basic math support
       *

       *

      The following basic math operators may be applied directly on numeric
       * data values:

       * + - * /
       * 


       * For example:
       *


      var tpl = new Ext.XTemplate(
          '<p>Name: {name}</p>',
          '<p>Kids: ',
          '<tpl for="kids">',
              '<tpl if="age &gt; 1">',  // <-- Note that the > is encoded
                  '<p>{#}: {name}</p>',  // <-- Auto-number each item
                  '<p>In 5 Years: {age+5}</p>',  // <-- Basic math
                  '<p>Dad: {parent.name}</p>',
              '</tpl>',
          '</tpl></p>'
      );
      tpl.overwrite(panel.body, data);


       *

       *

       *
       *
    *  * Execute arbitrary inline code with special built-in template variables
       *

       *

      Anything between {[ ... ]} is considered code to be executed
       * in the scope of the template. There are some special variables available in that code:
       *

          o  * values: The values in the current scope. If you are using
             * scope changing sub-templates, you can change what values is.

          o  * parent: The scope (values) of the ancestor template.

          o  * xindex: If you are in a looping template, the index of the
             * loop you are in (1-based).

          o  * xcount: If you are in a looping template, the total length
             * of the array you are looping.

          o  * fm: An alias for Ext.util.Format.

             * 
       * This example demonstrates basic row striping using an inline code block and the
       * xindex variable:


       *


      var tpl = new Ext.XTemplate(
          '<p>Name: {name}</p>',
          '<p>Company: {[values.company.toUpperCase() + ", " + values.title]}</p>',
          '<p>Kids: ',
          '<tpl for="kids">',
             '<div class="{[xindex % 2 === 0 ? "even" : "odd"]}">',
              '{name}',
              '</div>',
          '</tpl></p>'
      );
      tpl.overwrite(panel.body, data);
       * 


       *

       *

       *
    *  * Template member functions
       *

       *

      One or more member functions can be specified in a configuration
       * object passed into the XTemplate constructor for more complex processing:

       *


      var tpl = new Ext.XTemplate(
          '<p>Name: {name}</p>',
          '<p>Kids: ',
          '<tpl for="kids">',
              '<tpl if="this.isGirl(name)">',
                  '<p>Girl: {name} - {age}</p>',
              '</tpl>',
              // use opposite if statement to simulate 'else' processing:
              '<tpl if="this.isGirl(name) == false">',
                  '<p>Boy: {name} - {age}</p>',
              '</tpl>',
              '<tpl if="this.isBaby(age)">',
                  '<p>{name} is a baby!</p>',
              '</tpl>',
          '</tpl></p>',
          {
              // XTemplate configuration:
              compiled: true,
              disableFormats: true,
              // member functions:
              isGirl: function(name){
                  return name == 'Sara Grace';
              },
              isBaby: function(age){
                  return age < 1;
              }
          }
      );
      tpl.overwrite(panel.body, data);
       * 


       *

       *

       *
       * 


 *
 * @param {Mixed} config
 */
Ext.XTemplate = function(){
    Ext.XTemplate.superclass.constructor.apply(this, arguments);

    var me = this,
        s = me.html,
        re = /]*>((?:(?=([^<]+))\2|<(?!tpl\b[^>]*>))*?)<\/tpl>/,
        nameRe = /^]*?for="(.*?)"/,
        ifRe = /^]*?if="(.*?)"/,
        execRe = /^]*?exec="(.*?)"/,
        m,
        id = 0,
        tpls = [],
        VALUES = 'values',
        PARENT = 'parent',
        XINDEX = 'xindex',
        XCOUNT = 'xcount',
        RETURN = 'return ',
        WITHVALUES = 'with(values){ ';

    s = ['', s, ''].join('');

    while((m = s.match(re))){
        var m2 = m[0].match(nameRe),
            m3 = m[0].match(ifRe),
            m4 = m[0].match(execRe),
            exp = null,
            fn = null,
            exec = null,
            name = m2 && m2[1] ? m2[1] : '';

       if (m3) {
           exp = m3 && m3[1] ? m3[1] : null;
           if(exp){
               fn = new Function(VALUES, PARENT, XINDEX, XCOUNT, WITHVALUES + RETURN +(Ext.util.Format.htmlDecode(exp))+'; }');
           }
       }
       if (m4) {
           exp = m4 && m4[1] ? m4[1] : null;
           if(exp){
               exec = new Function(VALUES, PARENT, XINDEX, XCOUNT, WITHVALUES +(Ext.util.Format.htmlDecode(exp))+'; }');
           }
       }
       if(name){
           switch(name){
               case '.': name = new Function(VALUES, PARENT, WITHVALUES + RETURN + VALUES + '; }'); break;
               case '..': name = new Function(VALUES, PARENT, WITHVALUES + RETURN + PARENT + '; }'); break;
               default: name = new Function(VALUES, PARENT, WITHVALUES + RETURN + name + '; }');
           }
       }
       tpls.push({
            id: id,
            target: name,
            exec: exec,
            test: fn,
            body: m[1]||''
        });
       s = s.replace(m[0], '{xtpl'+ id + '}');
       ++id;
    }
    for(var i = tpls.length-1; i >= 0; --i){
        me.compileTpl(tpls[i]);
    }
    me.master = tpls[tpls.length-1];
    me.tpls = tpls;
};
Ext.extend(Ext.XTemplate, Ext.Template, {
    // private
    re : /\{([\w-\.\#]+)(?:\:([\w\.]*)(?:\((.*?)?\))?)?(\s?[\+\-\*\\]\s?[\d\.\+\-\*\\\(\)]+)?\}/g,
    // private
    codeRe : /\{\[((?:\\\]|.|\n)*?)\]\}/g,

    // private
    applySubTemplate : function(id, values, parent, xindex, xcount){
        var me = this,
            len,
            t = me.tpls[id],
            vs,
            buf = [];
        if ((t.test && !t.test.call(me, values, parent, xindex, xcount)) ||
            (t.exec && t.exec.call(me, values, parent, xindex, xcount))) {
            return '';
        }
        vs = t.target ? t.target.call(me, values, parent) : values;
        len = vs.length;
        parent = t.target ? values : parent;
        if(t.target && Ext.isArray(vs)){
            for(var i = 0, len = vs.length; i < len; i++){
                buf[buf.length] = t.compiled.call(me, vs[i], parent, i+1, len);
            }
            return buf.join('');
        }
        return t.compiled.call(me, vs, parent, xindex, xcount);
    },

    // private
    compileTpl : function(tpl){
        var fm = Ext.util.Format,
            useF = this.disableFormats !== true,
            sep = Ext.isGecko ? "+" : ",",
            body;

        function fn(m, name, format, args, math){
            if(name.substr(0, 4) == 'xtpl'){
                return "'"+ sep +'this.applySubTemplate('+name.substr(4)+', values, parent, xindex, xcount)'+sep+"'";
            }
            var v;
            if(name === '.'){
                v = 'values';
            }else if(name === '#'){
                v = 'xindex';
            }else if(name.indexOf('.') != -1){
                v = name;
            }else{
                v = "values['" + name + "']";
            }
            if(math){
                v = '(' + v + math + ')';
            }
            if (format && useF) {
                args = args ? ',' + args : "";
                if(format.substr(0, 5) != "this."){
                    format = "fm." + format + '(';
                }else{
                    format = 'this.call("'+ format.substr(5) + '", ';
                    args = ", values";
                }
            } else {
                args= ''; format = "("+v+" === undefined ? '' : ";
            }
            return "'"+ sep + format + v + args + ")"+sep+"'";
        }

        function codeFn(m, code){
            // Single quotes get escaped when the template is compiled, however we want to undo this when running code.
            return "'" + sep + '(' + code.replace(/\\'/g, "'") + ')' + sep + "'";
        }

        // branched to use + in gecko and [].join() in others
        if(Ext.isGecko){
            body = "tpl.compiled = function(values, parent, xindex, xcount){ return '" +
                   tpl.body.replace(/(\r\n|\n)/g, '\\n').replace(/'/g, "\\'").replace(this.re, fn).replace(this.codeRe, codeFn) +
                    "';};";
        }else{
            body = ["tpl.compiled = function(values, parent, xindex, xcount){ return ['"];
            body.push(tpl.body.replace(/(\r\n|\n)/g, '\\n').replace(/'/g, "\\'").replace(this.re, fn).replace(this.codeRe, codeFn));
            body.push("'].join('');};");
            body = body.join('');
        }
        eval(body);
        return this;
    },

    
/**
     * Returns an HTML fragment of this template with the specified values applied.
     * @param {Object} values The template values. Can be an array if your params are numeric (i.e. {0}) or an object (i.e. {foo: 'bar'})
     * @return {String} The HTML fragment
     */
    applyTemplate : function(values){
        return this.master.compiled.call(this, values, {}, 1, 1);
    },

    
/**
     * Compile the template to a function for optimized performance.  Recommended if the template will be used frequently.
     * @return {Function} The compiled function
     */
    compile : function(){return this;}

    
/**
     * @property re
     * @hide
     */
    
/**
     * @property disableFormats
     * @hide
     */
    
/**
     * @method set
     * @hide
     */

});
/**
 * Alias for {@link #applyTemplate}
 * Returns an HTML fragment of this template with the specified values applied.
 * @param {Object/Array} values The template values. Can be an array if your params are numeric (i.e. {0}) or an object (i.e. {foo: 'bar'})
 * @return {String} The HTML fragment
 * @member Ext.XTemplate
 * @method apply
 */
Ext.XTemplate.prototype.apply = Ext.XTemplate.prototype.applyTemplate;

/**
 * Creates a template from the passed element's value (display:none textarea, preferred) or innerHTML.
 * @param {String/HTMLElement} el A DOM element or its id
 * @return {Ext.Template} The created template
 * @static
 */
Ext.XTemplate.from = function(el){
    el = Ext.getDom(el);
    return new Ext.XTemplate(el.value || el.innerHTML);
};

