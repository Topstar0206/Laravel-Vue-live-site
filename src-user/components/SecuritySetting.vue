<template lang="pug">
section
	form.details-wrap(id="updateSecurityForm" @submit.prevent="updateSecurity($event)")
		ul.ul-reset.details-ul
			li
				h3.dashboard__content__title Password & Security Question
			li.gpf-input.one-half-wrap
				.one-half-input
					label New Password
					input(type="password" name="password")
				.one-half-input
					label Re-type New Password
					input(type="password" name="password_confirmation")
			li.gpf-input.one-half-wrap( v-for="n in total")
				.one-half-input
					label Security Question
					select.select--security(name="security_question[]" v-model="selectedQuestion[n]")
						option Security Question
						option(
							v-for="item in questions"
							:value="item.id") {{item.question}}
				.one-half-input
					label Unique Answer
					input(type="text" name="answer[]" v-model="selectedQuestionAnswer[n]")
			li
				button.button.button--accent(type="submit") Update
	.dashboard__footer
		h3.dashboard__content__title  Two-Factor Authentication
		p.two-step__desc(v-if="!authy_required") Enable two-factor authentication with GoogleAuth
		p.two-step__desc(v-if="!authy_required") Download and install the Google Authenticator app for Android or iOS(iPhone)
		.row.mt-3(v-if="!authy_required && qr")
			.col
				h3.dashboard__content__title  Set Up Two-Factor with Google Auth
				ul.ul-list
					li After installing the Google Authenticator app scan the QR code below and enter the verification code you see in the Google Authenticator app
					li.qr-code: img(:src="qr")
					li After scanning the QR Code, enter the verification code.
					li: input.code_input(name="code" v-model="code" v-validate="'required'")
					li
						|Your second-factor backup code is
						b  {{ key }}.
						|This can be used for manual setup, and is necessary to recover your account in case your mobile phone is lost or stolen.
					li
						a.button.button--accent#verification_button(@click="verifyCode") Verify Code
		.two-step.mt-4
			.two-step__header
				.two-step__txt
					h3.two-step__title Steps to enable 2FA
						a(href="#nogo" data-tooltip="Two Factor Authentication, also known as 2FA, is an extra layer of security that is known as \"multi factor authentication\" that requires not only a password and username but also something that only that user has on them, i.e. a piece of information only they should know or have immediately to hand - such as one time code generated by an app.").two-step__help
							i.fas.fa-question
					p.two-step__desc This will activate an extra layer of security to sign in to your account via the Google authenticator app on your phone
				.activate
					a.button.button--accent(@click="toggle2FA('disable')" v-if="authy_required") Deactivate Now
					a.button.button--accent(@click="toggle2FA('enable')" v-if="!authy_required") Activate Now
			ul.ul-reset.steps
				li
					.step__img
						img(src="~Images/two-step-1.png")
					p.step__txt Enter your password like usual
				li
					i.fas.fa-long-arrow-alt-right.fa-2x

				li
					.step__img
						img(src="~Images/two-step-2.png")
					p.step__txt Generate a new code from your authenticator app
				li
					i.fas.fa-long-arrow-alt-right.fa-2x
				li
					.step__img
						img(src="~Images/two-step-3.png")
					p.step__txt Enter authentication code and complete sign in
</template>

<script>
import m_common from "Mixins/common";

export default {
    components: {},
    props: [],
    mixins: [m_common],
    data() {
        return {
            total: 3,
            authy_required: "",
            qr: "",
            key: "",
            code: "",
            questions: []
        };
    },
    created() {},
    mounted() {
        this.secQue();
        this.get2FA();
    },
    computed: {
        selectedQuestion() {
            var selectedQuestion = {};
            for (var i = 1; i <= this.total; i++) {
                selectedQuestion[i] = '';
            }
            return selectedQuestion;
        },
        selectedQuestionAnswer() {
            var selectedQuestionAnswer = {};
            for (var i = 1; i <= this.total; i++) {
                selectedQuestionAnswer[i] = '';
            }
            return selectedQuestionAnswer;
        }
    },
    methods: {
        get2FA() {
            var _this = this;
            _this.apiGet({
                url: `${_this.apiUrl()}/twofactor/status`,
                success(data) {
                    _this.authy_required = data.authy_required;
                }
            });
        },
        toggle2FA(status) {
            var _this = this;
            _this.apiPost({
                url: `${_this.apiUrl()}/twofactor/${status}`,
                success(data) {
                    if (status == "enable") {
                        _this.qr = data['qr'];
                        _this.key = data['secret'];
                    } else {
                        _this.qr = '';
                        _this.key = '';
                    }
                    _this.get2FA();
                }
            });
        },

        updateSecurity($event) {
            var _this = this;
            _this.formSubmit({
                url: `${_this.apiUrl()}/security-update`,
                validator: _this.$validator,
                formId: $event.target.id,
                success(data) {}
            });
        },
        secQue() {
            var _this = this;
            _this.apiGet({
                url: `${_this.apiUrl()}/security-question`,
                success(data) {
                    var i = 1;
                    _this.questions = data;
                    _this.questions.forEach((item, index) => {
                        if (item.answer) {
                            _this.selectedQuestion[i] = item.id;
                            _this.selectedQuestionAnswer[i] = item.answer;
                            i++;
                        }
                    });
                }
            });
        },
        verifyCode() {
            var _this = this;
            _this.apiPost({
                url: `${_this.apiUrl()}/twofactor/verify`,
                data: {
                    secret: _this.key,
                    code: _this.code
                },
                buttonId: 'verification_button',
                success(data) {
                    _this.code = "";
                    _this.get2FA();
                }
            });
        }
    }
};
</script>

<style lang="css">
.ul-list{
	list-style-type: none;
	padding: 1px;
}
.ul-list li{
	margin: 1em 0;
}
.qr-code{
	height:50%;
}
.code_input{
	height: 40px;
	width: 30%;
	padding: 1px;
}
</style>
