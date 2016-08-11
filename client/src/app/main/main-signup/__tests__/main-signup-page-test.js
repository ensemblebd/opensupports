const MainSignupPage = requireUnit('app/main/main-signup/main-signup-page', {
    'react-google-recaptcha': ReactMock()
});


describe('Signup page', function () {

    afterEach(function () {
        UserStore.isLoggedIn.returns(false);
    });

    it('should trigger common action if user is currently logged', function () {
        CommonActions.logged.reset();
        UserStore.isLoggedIn.returns(true);

        TestUtils.renderIntoDocument(<MainSignupPage />);
        expect(CommonActions.logged).to.have.been.called;
    });

    it('should not trigger common action user if is not logged', function () {
        CommonActions.logged.reset();
        UserStore.isLoggedIn.returns(false);

        TestUtils.renderIntoDocument(<MainSignupPage />);
        expect(CommonActions.logged).to.not.have.been.called;
    });
});