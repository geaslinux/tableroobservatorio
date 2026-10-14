<?php namespace Myth\Auth\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Session\Session;
use Myth\Auth\Config\Auth as AuthConfig;
use Myth\Auth\Entities\User;
use Myth\Auth\Models\UserModel;
use Myth\Auth\Authorization\PermissionModel;
use Myth\Auth\Authorization\GroupModel;

use Config\Services;

class AuthController extends Controller
{
	protected $auth;

	/**
	 * @var AuthConfig
	 */
	protected $config;

	/**
	 * @var Session
	 */
	protected $session;
	protected $schema = '';

	public function __construct()
	{
		// Most services in this controller require
		// the session to be started - so fire it up!
		$this->session = service('session');

		$this->config = config('Auth');
		$this->auth = service('authentication');
	}

	//--------------------------------------------------------------------
	// Login/outc:\laragon\www\obs
	//--------------------------------------------------------------------

	public function list_users()
	{
		$users = model(UserModel::class);
		return $this->_render($this->config->views['listUsers'], [
		//return view('admin/detenido_list',[
			'users' => $users->orderBy('created_at','ASC')->paginate($this->config->regPerPage),
			'pager' => $users->pager,			
		]);
	}

	public function list_permisos($id)
	{
		$permisos = model(UserModel::class);
		return $this->_render($this->config->views['listPermisosUser'], [
			'permisos' => $permisos->orderBy('created_at','ASC')->join($this->schema.'auth_users_permissions aup', $this->schema.'users.id = aup.user_id')->join($this->schema.'auth_permissions ap', 'aup.permission_id = ap.id')->where($this->schema.'users.id', $id)->paginate($this->config->regPerPage),
			'pager' => $permisos->pager,			
		]);
	}
	
	public function create_permiso($id_user)
	{

		helper('form');		
		$model = model(PermissionModel::class);
		//$option = $model->select('GROUP_CONCAT("\"",id, "\":\"", name, "\"") as opciones')->findAll();//string_agg
		//$option = $model->select("STRING_AGG('\"' || id || '\":\"' || name || '\"', ',') as opciones")->findAll();

		$query = 'JSON_ARRAYAGG(JSON_OBJECT(id,name)) as opciones';
		$option = $model->select($query)->findAll();
		
		$formato_option = $option[0]['opciones'];
		$formato_option = str_replace("{", "", $formato_option);
		$formato_option = str_replace("}", "", $formato_option);
		$formato_option = str_replace("[", "{", $formato_option);
		$formato_option = str_replace("]", "}", $formato_option);		

		//$json2 = '{'.$option[0]['opciones'].'}';
		//$opciones = json_decode($json2, true);		
		$opciones = json_decode($formato_option, true);		

		return $this->_render($this->config->views['formPermisosUser'], [
			'formRoute' => 'store_permiso',
			'titulo' => 'Asignar nuevo permiso',
			'option' => $opciones,
		]);
	}

	public function store_permiso(){
		$model = model(PermissionModel::class);
		$model->addPermissionToUser($this->request->getVar('permission_id'), $this->request->getVar('user_id'));
		if($this->request->getVar('iframe')){
			Services::session()->setFlashdata('msg', [
				'type' => 'success',
				'body' => 'El permiso fue asignado correctamente',
			]);
			echo "<script>var soyTuPadre = window.parent; soyTuPadre.postMessage('reload', '*');</script>";
		}else{			
			return redirect()->to(base_url(route_to('permisos_list', $this->request->getVar('user_id'))))->with('msg',[
				'type' => 'success',
				'body' => 'El permiso fue asignado correctamente',
			]);
		}		
	}

	public function destroy_permiso(){
		$model = model(PermissionModel::class);
		$model->removePermissionFromUser($this->request->getVar('permission_id'), $this->request->getVar('user_id'));
		if($this->request->getVar('iframe')){
			Services::session()->setFlashdata('msg', [
				'type' => 'success',
				'body' => 'El permiso fue eliminado correctamente',
			]);
			echo "<script>var soyTuPadre = window.parent; soyTuPadre.postMessage('reload', '*');</script>";
		}else{			
			return redirect()->to(base_url(route_to('permisos_list', $this->request->getVar('user_id'))))->with('msg',[
				'type' => 'success',
				'body' => 'El permiso fue eliminado correctamente',
			]);
		}		
	}	


	public function list_group_user($id)
	{
		$permisos = model(UserModel::class);
		return $this->_render($this->config->views['listGroupUser'], [
			'grupos' => $permisos->orderBy('created_at','ASC')->join($this->schema.'auth_groups_users agu', $this->schema.'users.id = agu.user_id')->join($this->schema.'auth_groups ag', 'agu.group_id = ag.id')->where($this->schema.'users.id', $id)->paginate($this->config->regPerPage),
			'pager' => $permisos->pager,			
		]);
	}
	
	public function create_group_user($id_user)
{
    helper('form');        
    $model = model(GroupModel::class);
    
    // Detectar el tipo de base de datos
    $db = \Config\Database::connect();
    $dbType = $db->DBDriver;
    
    if ($dbType == 'Postgre') {
        $option = $model->select("STRING_AGG('\"' || id || '\":\"' || name || '\"', ',') as opciones")->findAll();
    } else {
        $option = $model->select('GROUP_CONCAT("\"",id, "\":\"", name, "\"") as opciones')->findAll();
    }
    
    $json2 = '{'.$option[0]->opciones.'}';
    $opciones = json_decode($json2, true);        

    return $this->_render($this->config->views['formGroupUser'], [
        'formRoute' => 'store_group_user',
        'titulo' => 'Asignar nuevo grupo',
        'option' => $opciones,
    ]);
}

	public function store_group_user(){
		$model = model(GroupModel::class);
		$model->addUserToGroup($this->request->getVar('user_id'), $this->request->getVar('group_id'));
		if($this->request->getVar('iframe')){
			Services::session()->setFlashdata('msg', [
				'type' => 'success',
				'body' => 'El grupo fue asignado correctamente',
			]);
			echo "<script>var soyTuPadre = window.parent; soyTuPadre.postMessage('reload', '*');</script>";
		}else{			
			return redirect()->to(base_url(route_to('group_list_user', $this->request->getVar('user_id'))))->with('msg',[
				'type' => 'success',
				'body' => 'El grupo fue asignado correctamente',
			]);
		}		
	}

	public function destroy_group_user(){
		$model = model(GroupModel::class);
		$model->removeUserFromGroup($this->request->getVar('user_id'), $this->request->getVar('group_id'));
		if($this->request->getVar('iframe')){
			Services::session()->setFlashdata('msg', [
				'type' => 'success',
				'body' => 'El grupo fue eliminado correctamente',
			]);
			echo "<script>var soyTuPadre = window.parent; soyTuPadre.postMessage('reload', '*');</script>";
		}else{			
			return redirect()->to(base_url(route_to('group_list_user', $this->request->getVar('user_id'))))->with('msg',[
				'type' => 'success',
				'body' => 'El grupo fue eliminado correctamente',
			]);
		}		
	}

	public function list_groups()
	{
		$model = model(GroupModel::class);
		return $this->_render($this->config->views['listGroups'], [
			'grupos' => $model->paginate($this->config->regPerPage),
			'pager' => $model->pager,			
		]);
	}

	public function create_group()
	{

		return $this->_render($this->config->views['formGroup'], [
			'formRoute' => 'store_group',
			'titulo' => 'Nuevo grupo',
		]);
	}	

	public function store_group(){

		if(!$this->valida_group()){
			return redirect()->back()->withInput()
				->with('msg', [
					'type' => 'error',
					'body' => 'Tienes campos incorrectos',
				])
				->with('errors', $this->validator->getErrors());
		}

		$model = model(GroupModel::class);
		$model->save([
			'name' => $this->request->getVar('name'), 
			'description' => $this->request->getVar('description'),
		]);

		return redirect()->to(base_url(route_to('groups_list')))->with('msg',[
			'type' => 'success',
			'body' => 'El grupo fue creado correctamente',
		]);
	}

	public function destroy_group(){
		$model = model(GroupModel::class);
		$model->delete($this->request->getVar('id'));

		return redirect('groups_list')->with('msg',[
			'type' => 'success',
			'body' => 'El grupo fue eliminado correctamente',
		]);			
	}

	public function valida_group(){
		return $this->validate([
			'name'=>'required',
            'description'=>'required',
		]);
	}
	
	public function list_groups_permisos($id)
	{
		$model = model(GroupModel::class);
		return $this->_render($this->config->views['listPermisosGroup'], [
			'permisos' => $model->join($this->schema.'auth_groups_permissions agp', $this->schema.'auth_groups.id = agp.group_id')->join($this->schema.'auth_permissions ap', 'agp.permission_id = ap.id')->where($this->schema.'auth_groups.id', $id)->paginate($this->config->regPerPage),
			'pager' => $model->pager,			
		]);
	}	

	public function create_group_permiso($id_group)
	{

		helper('form');		
		$model = model(PermissionModel::class);
		//$query = 'GROUP_CONCAT("\"",id,"\":",name,"\"") as opciones';
		$query = 'JSON_ARRAYAGG(JSON_OBJECT(id,name)) as opciones';
		$option = $model->select($query)->findAll();
		//$option = $model->select('GROUP_CONCAT("\"",id, "\":\"", name, "\"") as opciones')->findAll();//string_agg
		$formato_option = $option[0]['opciones'];
		$formato_option = str_replace("{", "", $formato_option);
		$formato_option = str_replace("}", "", $formato_option);
		$formato_option = str_replace("[", "{", $formato_option);
		$formato_option = str_replace("]", "}", $formato_option);		
		//$option = $model->select("STRING_AGG('\"' || id || '\":\"' || name || '\"', ',') as opciones")->findAll();		
		//$json2 = '{'.$option[0]['opciones'].'}';				
		//$opciones = json_decode($json2, true);
		$opciones = json_decode($formato_option, true);								

		return $this->_render($this->config->views['formPermisosGroup'], [
			'formRoute' => 'store_group_permiso',
			'titulo' => 'Asignar nuevo permiso a grupo',
			'option' => $opciones,
		]);
	}

	public function store_group_permiso(){
		$model = model(GroupModel::class);
		$model->addPermissionToGroup($this->request->getVar('permission_id'), $this->request->getVar('group_id'));
		if($this->request->getVar('iframe')){
			Services::session()->setFlashdata('msg', [
				'type' => 'success',
				'body' => 'El permiso fue asignado correctamente',
			]);
			echo "<script>var soyTuPadre = window.parent; soyTuPadre.postMessage('reload', '*');</script>";
		}else{			
			return redirect()->to(base_url(route_to('groups_permisos_list', $this->request->getVar('group_id'))))->with('msg',[
				'type' => 'success',
				'body' => 'El permiso fue asignado correctamente',
			]);
		}		
	}

	public function destroy_group_permiso(){
		$model = model(GroupModel::class);
		$model->removePermissionFromGroup($this->request->getVar('permission_id'), $this->request->getVar('group_id'));
		if($this->request->getVar('iframe')){
			Services::session()->setFlashdata('msg', [
				'type' => 'success',
				'body' => 'El permiso fue eliminado correctamente',
			]);
			echo "<script>var soyTuPadre = window.parent; soyTuPadre.postMessage('reload', '*');</script>";
		}else{			
			return redirect()->to(base_url(route_to('groups_permisos_list', $this->request->getVar('group_id'))))->with('msg',[
				'type' => 'success',
				'body' => 'El permiso fue eliminado correctamente',
			]);
		}		
	}	

	/**
	 * Displays the login form, or redirects
	 * the user to their destination/home if
	 * they are already logged in.
	 */
	public function login()
	{
		// No need to show a login form if the user
		// is already logged in.
		if ($this->auth->check())
		{
			$redirectURL = session('redirect_url') ?? site_url('/admin/inicio');
			unset($_SESSION['redirect_url']);

			return redirect()->to($redirectURL);
		}

        // Set a return URL if none is specified
        $_SESSION['redirect_url'] = session('redirect_url') ?? previous_url() ?? site_url('/');

		return $this->_render($this->config->views['login'], ['config' => $this->config]);
	}

	/**
	 * Attempts to verify the user's credentials
	 * through a POST request.
	 */
	public function attemptLogin()
	{
		$rules = [
			'login'	=> 'required',
			'password' => 'required',
		];
		if ($this->config->validFields == ['email'])
		{
			$rules['login'] .= '|valid_email';
		}

		if (! $this->validate($rules))
		{
			return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
		}

		$login = $this->request->getPost('login');
		$password = $this->request->getPost('password');
		$remember = (bool)$this->request->getPost('remember');

		// Determine credential type
		$type = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

		// Try to log them in...
		if (! $this->auth->attempt([$type => $login, 'password' => $password], $remember))
		{
			return redirect()->back()->withInput()->with('error', $this->auth->error() ?? lang('Auth.badAttempt'));
		}

		// Is the user being forced to reset their password?
		if ($this->auth->user()->force_pass_reset === true)
		{
			return redirect()->to(route_to('reset-password') .'?token='. $this->auth->user()->reset_hash)->withCookies();
		}

		$redirectURL = session('redirect_url') ?? site_url('/');
		unset($_SESSION['redirect_url']);

		return redirect()->to($redirectURL)->withCookies()->with('message', lang('Auth.loginSuccess'));
	}

	/**
	 * Log the user out.
	 */
	public function logout()
	{
		if ($this->auth->check())
		{
			$this->auth->logout();
		}

		return redirect()->to(site_url('/'));
	}

	//--------------------------------------------------------------------
	// Register
	//--------------------------------------------------------------------

	/**
	 * Displays the user registration page.
	 */
	public function register()
	{
        // check if already logged in.
		if ($this->auth->check())
		{
			//return redirect()->back(); //se comenta para desentralizar el registro del inicio de session
		}

        // Check if registration is allowed
		/*
		if (! $this->config->allowRegistration)
		{
			return redirect()->back()->withInput()->with('error', lang('Auth.registerDisabled'));
		}
		*/

		return $this->_render($this->config->views['register'], ['config' => $this->config]);
	}


	/**
	 * Attempt to register a new user.
	 */
	public function attemptRegister()
	{
		// Check if registration is allowed
		if (! $this->config->allowRegistration)
		{
			//return redirect()->back()->withInput()->with('error', lang('Auth.registerDisabled'));
		}

		$users = model(UserModel::class);

		// Validate basics first since some password rules rely on these fields
		$rules = [
			'username' => "required|alpha_numeric_space|min_length[3]|max_length[30]|is_unique[{$this->schema}users.username]",
			'email'    => "required|valid_email|is_unique[{$this->schema}users.email]",
		];

		if (! $this->validate($rules))
		{
			return redirect()->back()->withInput()
				->with('msg', [
					'type' => 'danger',
					'body' => 'Tienes campos incorrectos',
				])				
				->with('errors', $this->validator->getErrors());			
		}

		// Validate passwords since they can only be validated properly here
		$rules = [
			'password'     => 'required|strong_password',
			'pass_confirm' => 'required|matches[password]',
		];

		if (! $this->validate($rules))
		{
			return redirect()->back()->withInput()
				->with('msg', [
					'type' => 'danger',
					'body' => 'Tienes campos incorrectos',
				])				
				->with('errors', $this->validator->getErrors());
		}

		// Save the user
		$allowedPostFields = array_merge(['password'], $this->config->validFields, $this->config->personalFields);
		$user = new User($this->request->getPost($allowedPostFields));

		$this->config->requireActivation === null ? $user->activate() : $user->generateActivateHash();

		// Ensure default group gets assigned if set
        if (! empty($this->config->defaultUserGroup)) {
            $users = $users->withGroup($this->config->defaultUserGroup);
        }

		if (! $users->save($user))
		{
			return redirect()->back()->withInput()->with('errors', $users->errors());
		}

		if ($this->config->requireActivation !== null)
		{
			$activator = service('activator');
			$sent = $activator->send($user);

			if (! $sent)
			{
				return redirect()->back()->withInput()->with('error', $activator->error() ?? lang('Auth.unknownError'));
			}

			// Success!
			return redirect()->route('login')->with('message', lang('Auth.activationSuccess'));
		}

		// Success!
		//return redirect()->route('login')->with('message', lang('Auth.registerSuccess'));
		return redirect()->route('list_users')
			->with('msg', [
				'type' => 'danger',
				'body' => 'Usuario Creado correctamente',
			]);
		
	}

	//--------------------------------------------------------------------
	// Forgot Password
	//--------------------------------------------------------------------

	/**
	 * Displays the forgot password form.
	 */
	public function forgotPassword()
	{
		if ($this->config->activeResetter === null)
		{
			return redirect()->route('login')->with('error', lang('Auth.forgotDisabled'));
		}

		return $this->_render($this->config->views['forgot'], ['config' => $this->config]);
	}

	/**
	 * Attempts to find a user account with that password
	 * and send password reset instructions to them.
	 */
	public function attemptForgot()
	{
		if ($this->config->activeResetter === null)
		{
			return redirect()->route('login')->with('error', lang('Auth.forgotDisabled'));
		}

		$users = model(UserModel::class);

		$user = $users->where('email', $this->request->getPost('email'))->first();

		if (is_null($user))
		{
			return redirect()->back()->with('error', lang('Auth.forgotNoUser'));
		}

		// Save the reset hash /
		$user->generateResetHash();
		$users->save($user);

		$resetter = service('resetter');
		$sent = $resetter->send($user);

		if (! $sent)
		{
			return redirect()->back()->withInput()->with('error', $resetter->error() ?? lang('Auth.unknownError'));
		}

		return redirect()->route('reset-password')->with('message', lang('Auth.forgotEmailSent'));
	}

	/**
	 * Displays the Reset Password form.
	 */
	public function resetPassword()
	{
		if ($this->config->activeResetter === null)
		{
			return redirect()->route('login')->with('error', lang('Auth.forgotDisabled'));
		}

		$token = $this->request->getGet('token');

		return $this->_render($this->config->views['reset'], [
			'config' => $this->config,
			'token'  => $token,
		]);
	}

	/**
	 * Verifies the code with the email and saves the new password,
	 * if they all pass validation.
	 *
	 * @return mixed
	 */
	public function attemptReset()
	{
		if ($this->config->activeResetter === null)
		{
			return redirect()->route('login')->with('error', lang('Auth.forgotDisabled'));
		}

		$users = model(UserModel::class);

		// First things first - log the reset attempt.
		$users->logResetAttempt(
			$this->request->getPost('email'),
			$this->request->getPost('token'),
			$this->request->getIPAddress(),
			(string)$this->request->getUserAgent()
		);

		$rules = [
			'token'		=> 'required',
			'email'		=> 'required|valid_email',
			'password'	 => 'required|strong_password',
			'pass_confirm' => 'required|matches[password]',
		];

		if (! $this->validate($rules))
		{
			return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
		}

		$user = $users->where('email', $this->request->getPost('email'))
					  ->where('reset_hash', $this->request->getPost('token'))
					  ->first();

		if (is_null($user))
		{
			return redirect()->back()->with('error', lang('Auth.forgotNoUser'));
		}

        // Reset token still valid?
        if (! empty($user->reset_expires) && time() > $user->reset_expires->getTimestamp())
        {
            return redirect()->back()->withInput()->with('error', lang('Auth.resetTokenExpired'));
        }

		// Success! Save the new password, and cleanup the reset hash.
		$user->password 		= $this->request->getPost('password');
		$user->reset_hash 		= null;
		$user->reset_at 		= date('Y-m-d H:i:s');
		$user->reset_expires    = null;
        $user->force_pass_reset = false;
		$users->save($user);

		return redirect()->route('login')->with('message', lang('Auth.resetSuccess'));
	}

	/**
	 * Activate account.
	 *
	 * @return mixed
	 */
	public function activateAccount()
	{
		$users = model(UserModel::class);

		// First things first - log the activation attempt.
		$users->logActivationAttempt(
			$this->request->getGet('token'),
			$this->request->getIPAddress(),
			(string) $this->request->getUserAgent()
		);

		$throttler = service('throttler');

		if ($throttler->check(md5($this->request->getIPAddress()), 2, MINUTE) === false)
        {
			return service('response')->setStatusCode(429)->setBody(lang('Auth.tooManyRequests', [$throttler->getTokentime()]));
        }

		$user = $users->where('activate_hash', $this->request->getGet('token'))
					  ->where('active', 0)
					  ->first();

		if (is_null($user))
		{
			return redirect()->route('login')->with('error', lang('Auth.activationNoUser'));
		}

		$user->activate();

		$users->save($user);

		return redirect()->route('login')->with('message', lang('Auth.registerSuccess'));
	}

	/**
	 * Resend activation account.
	 *
	 * @return mixed
	 */
	public function resendActivateAccount()
	{
		if ($this->config->requireActivation === null)
		{
			return redirect()->route('login');
		}

		$throttler = service('throttler');

		if ($throttler->check(md5($this->request->getIPAddress()), 2, MINUTE) === false)
		{
			return service('response')->setStatusCode(429)->setBody(lang('Auth.tooManyRequests', [$throttler->getTokentime()]));
		}

		$login = urldecode($this->request->getGet('login'));
		$type = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

		$users = model(UserModel::class);

		$user = $users->where($type, $login)
					  ->where('active', 0)
					  ->first();

		if (is_null($user))
		{
			return redirect()->route('login')->with('error', lang('Auth.activationNoUser'));
		}

		$activator = service('activator');
		$sent = $activator->send($user);

		if (! $sent)
		{
			return redirect()->back()->withInput()->with('error', $activator->error() ?? lang('Auth.unknownError'));
		}

		// Success!
		return redirect()->route('login')->with('message', lang('Auth.activationSuccess'));

	}

	protected function _render(string $view, array $data = [])
	{
		return view($view, $data);
	}
}
