import app from 'flarum/admin/app';
import Button from 'flarum/common/components/Button';
import extractText from 'flarum/common/utils/extractText';

app.initializers.add('sycho/flarum-force-password-reset', () => {
  app.extensionData
    .for('sycho-force-password-reset')
    .registerSetting(() => (
      <div className="Form-group">
        <label>{app.translator.trans('sycho-force-password-reset.admin.force_password_reset.title')}</label>
        <p className="helpText">{app.translator.trans('sycho-force-password-reset.admin.force_password_reset.description')}</p>
        <Button
          className="Button"
          icon="fas fa-key"
          onclick={() => {
            if (confirm(extractText(app.translator.trans('sycho-force-password-reset.admin.force_password_reset.reset_all_confirm')))) {
              app
                .request({
                  method: 'POST',
                  url: app.forum.attribute('apiUrl') + '/force-password-reset',
                })
                .then(() =>
                  app.alerts.show(
                    {
                      type: 'success',
                    },
                    app.translator.trans('sycho-force-password-reset.admin.force_password_reset.reset_all_success')
                  )
                );
            }
          }}
        >
          {app.translator.trans('sycho-force-password-reset.admin.force_password_reset.button_label')}
        </Button>
      </div>
    ))
    .registerSetting(() => (
      <div className="Form-group ForceResetPassword-resetCount">
        <label>{app.translator.trans('sycho-force-password-reset.admin.reset_count.title')}</label>
        <p className="helpText">{app.translator.trans('sycho-force-password-reset.admin.reset_count.description')}</p>
        <span className="ForceResetPassword-resetCount-value">
          {app.translator.trans('sycho-force-password-reset.admin.reset_count.value', {
            count: new Intl.NumberFormat('en-IN', { maximumSignificantDigits: 3 }).format(
              app.data['sycho-force-password-reset.reset-done-count'] as number
            ),
          })}
        </span>
      </div>
    ));
});
