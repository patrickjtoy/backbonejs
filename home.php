<!DOCTYPE html>
<html>
	<head>
		<title></title>
		<link rel="stylesheet" type="text/css" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" />
	</head>
	<body>
		<div class="container">
			<h1>User Manager</h1>
			<hr />
			<div class="page"></div>
		</div>

		<script type="text/template" id="user-list-template">

			<a href="#/new" class="btn btn-primary">New User</a>

			<hr />

			<table class="table table-striped"
				<thead>
					<tr>
						<th>First Name</th>
						<th>Last Name</th>
						<th>Age</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<% _.each(users, function(user) { %>
						<tr>
							<td><%= user.get('firstname') %></td>
							<td><%= user.get('lastname') %></td>
							<td><%= user.get('age') %></td>
							<td><a href="#/edit/<%= user.id %>" class="btn btn-default">Edit</a></td>
						</tr>
					<% }); %>
				</tbody>
			</table>

		</script>

		<script type="text/template" id="edit-user-template">

		<form role="form" class="edit-user-form">
			<legend><%= user ? 'Update' : 'Create' %> User</legend>
			<div class="form-group">
				<label>First Name</label>
				<input type="text" name="firstname" class="form-control" placeholder="John" value="<%= user ? user.get('firstname') : '' %>" />
			</div>
			<div class="form-group">
				<label>Last Name</label>
				<input type="text" name="lastname" class="form-control" placeholder="Doe" value="<%= user ? user.get('lastname') : '' %>" />
			</div>
			<div class="form-group">
				<label>Age</label>
				<input type="text" name="age" class="form-control" placeholder="21" value="<%= user ? user.get('age') : '' %>" />
			</div>
			<hr />
			<button type="submit" class="btn btn-default"><%= user ? 'Update' : 'Create' %></button>
			<% if (user) { %>
				<input type="hidden" name="id" value="<%= user.id %>" />
				<button type="button" class="btn btn-danger delete">Delete</button>
			<% }; %>
		</form>

		</script>

		<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
		<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.6.0/underscore-min.js"></script>
		<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/backbone.js/1.1.2/backbone-min.js"></script>
		<script type="text/javascript">

		$.ajaxPrefilter(function(options, originalOptions, jqXHR) {
			options.url = 'http://usermanager.patrickjtoy.dev' + options.url;
		});

		$.fn.serializeObject = function() {
			var o = {};
			var a = this.serializeArray();
			$.each(a, function() {
				if (o[this.name] !== undefined) {
					if (!o[this.name].push) {
							o[this.name] = [o[this.name]];
					}
					o[this.name].push(this.value || '');
				} else {
						o[this.name] = this.value || '';
				}
			});
			return o;
		};

		var Users = Backbone.Collection.extend({
			url: '/users'
		});

		var User = Backbone.Model.extend({
			urlRoot: '/users'
		});

		var UserList = Backbone.View.extend({
			el: '.page',
			render: function() {
				var self = this;
				var users = new Users();
				users.fetch({
					success: function() {
						var template = _.template($('#user-list-template').html(), {users: users.models});
						self.$el.html(template);
					}
				});
			}
		});

		var EditUser = Backbone.View.extend({
			el: '.page',
			render: function(options) {
				var self = this;
				if (options.id) {
					self.user = new User({id: options.id});
					self.user.fetch({
						success: function(user) {
							console.log(user);
							var template = _.template($('#edit-user-template').html(), {user: user});
							self.$el.html(template);
						}
					});
				} else {
						var template = _.template($('#edit-user-template').html(), {user: null});
						this.$el.html(template);
				}
			},
			events: {
				'submit .edit-user-form': 'saveUser',
				'click .delete': 'deleteUser'
			},
			saveUser: function(ev) {
				var userDetails = $(ev.currentTarget).serializeObject();
				var user = new User();
				user.save(userDetails, {
					success: function(user) {
						router.navigate('', {trigger: true});
					}
				})
				console.log(userDetails);
				return false;
			},
			deleteUser: function(ev) {
				this.user.destroy({
					success: function() {
						router.navigate('', {trigger: true});
					}
				});
				return false;
			}
		});

		var Router = Backbone.Router.extend({
			routes: {
				'': 'home',
				'new': 'editUser',
				'edit/:id': 'editUser'
			}
		});

		var userList = new UserList();
		var editUser = new EditUser();

		var router = new Router();
		router.on('route:home', function() {
			userList.render();
		});
		router.on('route:editUser', function(id) {
			editUser.render({id: id});
		});

		Backbone.history.start();

		</script>
	</body>
</html>