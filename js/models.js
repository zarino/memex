Memex.Model.Item = Backbone.Model.extend({

  url: '/api/items'

})

Memex.Collection.Items = Backbone.Collection.extend({

  model: Memex.Model.Item

})