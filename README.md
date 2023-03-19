## GET STARTED

Before you can start using the application, you need to configure certain information from the **config.env** file. This includes setting up your database connection details, configuring authorization settings, and defining other important variables such as file upload limits and supported file types.

### Database Configuration
| Name | Description | Example |
|------|-------------|---------|
| `SERVER` | The hostname or IP address of the server where the database is hosted. | `localhost` |
| `USERNAME` | The username used to authenticate with the database server. | `root` |
| `PASSWORD` | The password used to authenticate with the database server. | `user@123` |
| `DATABASE` | The name of the database being connected to. | `crudtest` | 

### Basic Authorization Configuration
| Name | Description | Example |
|------|-------------|---------|
| `BASIC_AUTH` | A boolean value that determines whether basic authentication is enabled or disabled. | `false` |
| `BASIC_AUTH_USERNAME` | The username required to authenticate with the application when basic authentication is enabled. | `admin` |
| `BASIC_AUTH_PASSWORD` | The password required to authenticate with the application when basic authentication is enabled. | `admin@123` |

### File Upload Configuration
| Name | Description | Example |
|------|-------------|---------|
| `SUPPORTED_FILES` | A comma-separated list of file extensions that the application supports for file uploads. | `png,jpg` |
| `FILE_MAX_SIZE_IN_KB` | The maximum allowed size of uploaded files in kilobytes (KB). | `1024` |
| `FILE_MIN_SIZE_IN_KB` | The minimum allowed size of uploaded files in kilobytes (KB). | `10` |

To start using the CRUD (Create, Read, Update, Delete) functionality of your application, you need to have a database table that you want to perform these operations on. In this example, let's assume that you have already created a table in your database called users.

Once you have your `users` name table, you can access it through a URL like example.com/users. This URL represents the endpoint for all CRUD operations that can be performed on the `users` table. For example, to create a new user in the users table, you would send a POST request to example.com/users, with the user's details included in the request body.
### Methods
- CREATE
- READ
- UPDATE
- DELETE


#### Create (Json method)
Create a new user in the `users` table with the specified name.

| **Method** | **URL**           | **BODY**
|------------|------------------|------------------|
| POST       | example.com/users | json |

- **Body:**

```json
{
    "name": "Rohit"
}
```
Please note that this API endpoint assumes that you have a database table named `users` and that it has a column named `name` where the `Rohit` will be stored.

#### Create (Form method)
Create a new user in the `users` table with the specified name.
> Make sure you have defined `xform=true` in url

| **Method** | **URL**           | **Body** |
|------------|------------------|------------------|
| POST       | example.com/users**?xform=true** | form |

- **Body:**

```html
<form action="example.com/users" method="post">
	<input name="name" value="Rohit"/>
	<button type="submit">Add Record</button>
</form>
```
#### Image/File upload
If you want to send data from a form and upload files, you need to use a form request. Image uploads will only work if the request is sent as form data. To use form data for your request, include the parameter `xform=true`. Additionally, for the image file input field, use the parameter filefield=fieldname, where `fieldname` is the name of the file input **field name** and** column name** of table in your HTML form.
> Make sure you have defined `xform=true` in url
> Make sure you have defined `filefield=fieldname,fieldname` (separate by comma if multiple image) in url

| **Method** | **URL**           | **Body** |
|------------|------------------|------------------|
| POST       | example.com/users**?xform=true&filefield=profile** | form |

- **Body:**

```html
<form action="example.com/users" method="post" enctype="multipart/form-data">
	<input name="name" value="Rohit"/>
	<input name="profile" type="file"/>
	<button type="submit">Add Record</button>
</form>
```

So this Form will store `Rohit` value in `name` column, and selected image will be stored in **uploads/** folder and file name will be store in **profile** column of table.

#### READ
* Retrevie all data, This will return all the data from the table

| **Method** | **URL**        |
|------------|------------------|
| GET      | example.com/users |

* Retrevie data by filtering, This will return data where** id=1** & **name=Rohit**, like this you can filter your data like (`column=value`) 

| **Method** | **URL**        |
|------------|------------------|
| GET      | example.com/users**?id=1&name=Rohit** |

#### UPDATE

| **Method** | **URL**        |  **Body**        |
|------------|------------------|-----------------|
| PUT      | example.com/users**?id=1** | json |
|        |  | |
| PUT      | example.com/users**?email=rohit@gmail.com** | json |

- **Body :**

```json
{
    "name": "Rohit",
	"city":"Jaipur"
}
```
It will update column value as provided filter in param.

#### DELETE

| **Method** | **URL**        |  **Body**        |
|------------|------------------|-----------------|
| DELETE      | example.com/users | json |
- **Body:**

```json
{
    "id": 1
}
```

It will delete row where data **id==1**, you can provided multiple key in json

----
> ### Find Documentation on [POSTMAN](https://documenter.getpostman.com/view/6406548/2s93JzMLpM "POSTMAN") also