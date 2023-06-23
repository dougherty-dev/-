<?php declare(strict_types = 1);

defined('VERSION_DATE') or die();

trait Admin_common {
	use Common, Security, Headers;

	private function show_admin(): void {
		$this->init_markup();
		echo <<< EOT
{$this->show_admin_sidebar()}
{$this->show_admin_folio()}

EOT;
		$this->end_markup();
	}

	private function show_admin_sidebar(): string {
		return <<< EOT
			<div class="grid-sidebar">
				<div>
					<p class="sidebar_title">Navigate</p>
					<p><a href="/admin/"><button>Admin</button></a></p>
					<p><a href="/admin/sets/"><button>Sets</button></a></p>
					<p><a href="/admin/images/"><button>Images</button></a></p>
					<p><a href="/admin/themes/"><button>Themes</button></a></p>
					<p><a href="/admin/pages/"><button>Pages</button></a></p>
				</div>
				<div>
					<p class="sidebar_title">Admin</p>
					<p><a href="/"><button>Front</button></a>
						<button id="admin_logout">Logout</button></p>
				</div>
			</div> <!-- grid-sidebar -->
EOT;
	}

}
