<div class="row sub_footer">
  <div class="col-12 mt-5 mb-3">
    <div style="border-top:1px solid #1E1E1E"></div>
  </div>

  <div class="col-lg-6 col-12 d-flex ls-footer-label">
    <i class="ri-information-line me-2"></i>
    <p class="me-1">ᴵ</p>
    <div>
      <?= sprintf(gT("Go to %sglobal user management%s for general user management (add/edit/delete general users). %sIf you don't have permission please contact your administrator.%s"), '<a href="' . $this->createUrl('userManagement/index') . '" target="_blank">', '</a>', '<br>', '</br>'); ?>
    </div>
  </div>
</div>