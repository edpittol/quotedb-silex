$('.typeahead').typeahead({
  hint: true,
  highlight: true,
  minLength: 1
},
{
  name: 'authors',
  display: 'name',
  source: new Bloodhound({
	datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
	queryTokenizer: Bloodhound.tokenizers.whitespace,
	remote: {
	  url: '/authors',
	  wildcard: '%QUERY'
	}
  })
});

$('.typeahead').bind('typeahead:select', function(ev, suggestion) {
  $('#quote_author_id').val(suggestion.id);
});