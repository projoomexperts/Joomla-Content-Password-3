/**
* @version		$Id$ 2011/08/16 cpgroups.php
* @package		Contentpassword
* @copyright	ProgAndy
* @license		GNU General Public License 2 or later
*/
if (!CPGroupClass) {
var CPGroupClass = new Class({
	
	id: null, 
	name: null, 
	accordion: null, 
	
	initialize: function(id, name){
        this.id = id;
        this.name = name;
		this.accordion = new Fx.Accordion($$('div#'+id+' h3.cpgroup-toggler'), $$('div#'+id+' div.cpgroup-slider'));
		$$('div#'+id+' h3 a.cpicon-remove').addEvent('click', this.remove.bind(this));
		$$('div#'+id+' h3 a.cpicon-rename').addEvent('click', this.rename.bind(this)); 	
		$$('div#'+id+' a.cpgroup-add').addEvent('click', this.add.bind(this));
    }, 
    
    add: function (event) {
		event.stop();
        var name = prompt(CPGroupLang.askname, '');
		if (name == null || name == '') return;
		else if (!name.match(/^\w+$/)) {
			alert(CPGroupLang.invalidname);
			return;
		} else if ($(this.id + '-toggler-' + name) !== null) { 
			alert(CPGroupLang.nameexists);
			return;
		}
		var toggle = new Element('h3', { 'class': 'cpgroup-toggler', 'id': this.id + '-toggler-' + name, 'html': '<a href="javascript:void(0);">' + name + '</a>' });
		toggle.grab(new Element('a', {'class': 'cpicon-remove', 'text': ' ' + CPGroupLang.remove, 'title': CPGroupLang.remove, 
				'href': 'javascript:void(0);', 
				'events': {
					'click': this.remove.bind(this)
				}
			}));
		toggle.grab(new Element('a', {'class': 'cpicon-rename', 'text': ' ' + CPGroupLang.rename, 'title': CPGroupLang.rename, 
				'href': 'javascript:void(0);', 
				'events': {
					'click': this.rename.bind(this)
				}
			}));
		var content = new Element('div', { 'id': this.id + '-slider-' + name, 'class': 'cpgroup-slider' });
		content.grab(new Element('label', {'for': this.id + '-group-' + name + '-sql', 'text': CPGroupLang.sql }));
		content.grab(new Element('input', {'id': this.id + '-group-' + name + '-sql', 'name': this.name + '[' + name + '][sql]' }));
		content.grab(new Element('label', {'for': this.id + '-group-' + name + '-passwords', 'text': CPGroupLang.passwords }));
		content.grab(new Element('textarea', {'id': this.id + '-group-' + name + '-passwords', 'name': this.name + '[' + name + '][passwords]' }));
		
		$(this.id).grab(toggle);
		$(this.id).grab(content);
        this.accordion.display(-1);
        this.accordion.addSection(toggle, content).display(content);
    },
    remove: function (event) {
		event.stop();
        var name = $(event.target).getParent().id.slice(this.id.length + 9);
		if (!confirm(name + " - " + CPGroupLang.remove + "?")) return;
		var toggle = $(this.id + '-toggler-' + name);
		var content = $(this.id + '-slider-' + name);
		this.accordion.removeSection(toggle);
		toggle.dispose();
		content.dispose();
		this.accordion.display(0);
    },
    rename: function (event) {
		event.stop();
		var oldname = $(event.target).getParent().id.slice(this.id.length + 9);
		var name = prompt(CPGroupLang.askname, '');
		if (name == null || name == '') return;
		else if (!name.match(/^\w+$/)) {
			alert(CPGroupLang.invalidname);
			return;
		} else if ($(this.id + '-toggler-' + name) !== null) { 
			alert(CPGroupLang.nameexists);
			return;
		}
		var toggle = $(this.id + '-toggler-' + oldname);
		toggle.set('id', this.id + '-toggler-' + name);
		toggle.getElement('a').set('text', name);
		var content = $(this.id + '-slider-' + oldname);
		content.set('id', this.id + '-slider-' + name);
		content.getElement('#'+this.id + '-group-' + oldname + '-sql').set('id', this.id + '-group-' + name + '-sql')
			.set('name', this.name + '[' + name + '][sql]');
		content.getElement('#'+this.id + '-group-' + oldname + '-passwords').set('id', this.id + '-group-' + name + '-passwords')
			.set('name', this.name + '[' + name + '][passwords]');
		content.getElement('label[for$=sql]').set('for', this.id + '-group-' + name + '-sql');
		content.getElement('label[for$=passwords]').set('for', this.id + '-group-' + name + '-passwords');
	}
});
}