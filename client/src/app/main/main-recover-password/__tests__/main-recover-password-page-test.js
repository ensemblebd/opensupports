const SubmitButton = ReactMock();
const Button = ReactMock();
const Input = ReactMock();
const Form = ReactMock();
const Message = ReactMock();
const Widget = ReactMock();

const MainRecoverPasswordPage = requireUnit('app/main/main-recover-password/main-recover-password-page', {
    'core-components/submit-button': SubmitButton,
    'core-components/button': Button,
    'core-components/input': Input,
    'core-components/form': Form,
    'core-components/message': Message,
    'core-components/widget': Widget
});

describe('Recover Password form', function () {
    let recoverForm, inputs, component, submitButton;
    let query = {
        token: 'SOME_TOKEN',
        email: 'SOME_EMAIL'
    };

    beforeEach(function () {
        component = TestUtils.renderIntoDocument(
            <MainRecoverPasswordPage location={{query}}/>
        );
        recoverForm = TestUtils.scryRenderedComponentsWithType(component, Form)[0];
        inputs = TestUtils.scryRenderedComponentsWithType(component, Input);
        submitButton = TestUtils.scryRenderedComponentsWithType(component, SubmitButton)[0];
    });

    it('should trigger recoverPassword action when submitted', function () {
        UserActions.sendRecoverPassword.reset();
        recoverForm.props.onSubmit({password: 'MOCK_VALUE'});
        expect(UserActions.recoverPassword).to.have.been.calledWith({
            password: 'MOCK_VALUE',
            token: 'SOME_TOKEN',
            email: 'SOME_EMAIL'
        });
    });

    it('should set loading true in the form when submitted', function () {
        recoverForm.props.onSubmit({password: 'MOCK_VALUE'});
        expect(recoverForm.props.loading).to.equal(true);
    });

    it('should show message when recover fails', function () {
        component.onUserStoreChanged('INVALID_RECOVER');
        expect(recoverForm.props.loading).to.equal(false);

        let message = TestUtils.scryRenderedComponentsWithType(component, Message)[0];
        expect(message).to.not.equal(null);
        expect(message.props.type).to.equal('error');
        expect(message.props.children).to.equal('Invalid recover data');
    });

    it('should show message when recover success', function () {
        component.onUserStoreChanged('VALID_RECOVER');
        expect(recoverForm.props.loading).to.equal(false);

        let message = TestUtils.scryRenderedComponentsWithType(component, Message)[0];
        expect(message).to.not.equal(null);
        expect(message.props.type).to.equal('success');
        expect(message.props.children).to.equal('Password recovered successfully');
    });
});
