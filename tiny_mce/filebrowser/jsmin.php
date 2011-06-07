<?PHP
error_reporting(0);
ini_set('display_errors', 'off');

define('ORD_LF', 10);
define('ORD_SPACE', 32);

class JSMin
{
	var $a = '';
	var $b = '';
	var $input = '';
	var $inputIndex  = 0;
	var $inputLength = 0;
	var $lookAhead = null;
	var $output = array();

	function minify($js)
	{
		$jsmin = new JSMin($js);
		return $jsmin->jsminify();
	}

	function JSMin($input)
	{
		$this->input = $input;
		$this->inputLength = strlen($input);
	}

	function action($d)
	{
		switch($d)
		{
			case 1:
				$this->output[] = $this->a;
		
			case 2:
				$this->a = $this->b;
				if ($this->a === "'" || $this->a === '"')
				{
					for (;;)
					{
						$this->output[] = $this->a;
						$this->a = $this->get();
		
						if ($this->a === $this->b)
						{
							break;
						}
		
						if (ord($this->a) <= ORD_LF)
						{
							die('Unterminated string literal.');
						}
		
						if ($this->a === '\\')
						{
							$this->output[] = $this->a;
							$this->a = $this->get();
						}
					}
				}
		
			case 3:
				$this->b = $this->next();
		
				if ($this->b === '/' && ($this->a === '(' || $this->a === ',' || $this->a === '=' || $this->a === ':' || $this->a === '[' || $this->a === '!' || $this->a === '&' || $this->a === '|' || $this->a === '?'))
				{
					$this->output[] = $this->a;
					$this->output[] = $this->b;
		
					for (;;)
					{
						$this->a = $this->get();
						
						if ($this->a === '/')
						{
							break;
						}
						elseif ($this->a === '\\')
						{
							$this->output[] = $this->a;
							$this->a = $this->get();
						}
						elseif (ord($this->a) <= ORD_LF)
						{
							die('Unterminated regular expression literal.');
						}
		
						$this->output[] = $this->a;
					}
		
					$this->b = $this->next();
				}
		}
	}

	function get()
	{
		$c = $this->lookAhead;
		$this->lookAhead = null;
	
		if ($c === null)
		{
			if ($this->inputIndex < $this->inputLength)
			{
				$c = $this->input[$this->inputIndex];
				$this->inputIndex += 1;
			}
			else
			{
				$c = null;
			}
		}
	
		if ($c === "\r")
		{
			return "\n";
		}
	
		if ($c === null || $c === "\n" || ord($c) >= ORD_SPACE)
		{
			return $c;
		}
	
		return ' ';
	}

	function isAlphaNum($c)
	{
		return ord($c) > 126 || $c === '\\' || preg_match('/^[\w\$]$/', $c) === 1;
	}

	function jsminify()
	{
		$this->a = "\n";
		$this->action(3);
		
		while ($this->a !== null)
		{
			switch ($this->a)
			{
				case ' ':
					if ($this->isAlphaNum($this->b))
					{
						$this->action(1);
					}
					else
					{
						$this->action(2);
					}
					break;
		
				case "\n":
					switch ($this->b)
					{
						case '{':
						case '[':
						case '(':
						case '+':
						case '-':
							$this->action(1);
							break;
		
						case ' ':
							$this->action(3);
							break;
		
						default:
							if ($this->isAlphaNum($this->b))
							{
								$this->action(1);
							}
							else
							{
								$this->action(2);
							}
					}
					break;
		
				default:
					switch ($this->b)
					{
						case ' ':
							if ($this->isAlphaNum($this->a))
							{
								$this->action(1);
								break;
							}
		
							$this->action(3);
							break;
		
						case "\n":
							switch ($this->a)
							{
								case '}':
								case ']':
								case ')':
								case '+':
								case '-':
								case '"':
								case "'":
									$this->action(1);
									break;
		
								default:
									if ($this->isAlphaNum($this->a))
									{
										$this->action(1);
									}
									else
									{
										$this->action(3);
									}
							}
							break;
		
						default:
							$this->action(1);
							break;
					}
			}
		}
		
		return implode('', $this->output);
	}

	function next()
	{
		$c = $this->get();
		
		if ($c === '/')
		{
			switch($this->peek())
			{
				case '/':
					for (;;)
					{
						$c = $this->get();
						if (ord($c) <= ORD_LF)
						{
							return $c;
						}
					}
		
				case '*':
					$this->get();
					for (;;)
					{
						switch($this->get())
						{
							case '*':
								if ($this->peek() === '/')
								{
									$this->get();
									return ' ';
								}
								break;
		
							case null:
								die('Unterminated comment.');
						}
					}
		
				default:
					return $c;
			}
		}
		
		return $c;
	}

	function peek()
	{
		$this->lookAhead = $this->get();
		return $this->lookAhead;
	}
}

?>