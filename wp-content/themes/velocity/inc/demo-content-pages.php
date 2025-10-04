<?php
/**
 * Demo Content - Page Content Generators (Part 2)
 *
 * Services, Portfolio, and Contact page content
 *
 * @package Velocity
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Services Page Content
 */
function velocity_get_services_page_content() {
	return '
<!-- Hero -->
<div style="background: linear-gradient(135deg, #6C5CE7 0%, #00B894 100%); color: white; text-align: center; padding: 80px 20px; margin-bottom: 60px;">
	<h1 style="font-size: 3rem; color: white; margin-bottom: 15px;">Our Services</h1>
	<p style="font-size: 1.25rem; opacity: 0.95;">Comprehensive digital solutions to grow your business</p>
</div>

<!-- Services Grid -->
<div style="padding: 60px 20px;">
	<div style="max-width: 1200px; margin: 0 auto;">
		<div style="text-align: center; margin-bottom: 50px;">
			<h2 style="font-size: 2.5rem; margin-bottom: 15px;">What We Do</h2>
			<p style="color: #636E72;">From concept to launch, we handle every aspect of your digital presence</p>
		</div>

		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 60px; margin-bottom: 60px;">
			<div style="display: flex; gap: 30px; align-items: flex-start;">
				<img src="https://source.unsplash.com/600x600/?web,design,ui" alt="Web Design" style="width: 300px; height: 300px; object-fit: cover; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);" />
				<div>
					<h3 style="margin-bottom: 15px;">Web Design</h3>
					<p style="margin-bottom: 15px;">Create stunning, user-friendly websites that convert visitors into customers. Our design process focuses on aesthetics, usability, and brand consistency.</p>
					<ul style="list-style: none; padding: 0;">
						<li style="padding: 8px 0; border-bottom: 1px solid #F8F9FA;">✓ Responsive Design</li>
						<li style="padding: 8px 0; border-bottom: 1px solid #F8F9FA;">✓ UI/UX Design</li>
						<li style="padding: 8px 0; border-bottom: 1px solid #F8F9FA;">✓ Wireframing & Prototyping</li>
						<li style="padding: 8px 0;">✓ Design Systems</li>
					</ul>
				</div>
			</div>

			<div style="display: flex; gap: 30px; align-items: flex-start;">
				<img src="https://source.unsplash.com/600x600/?coding,development" alt="Development" style="width: 300px; height: 300px; object-fit: cover; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);" />
				<div>
					<h3 style="margin-bottom: 15px;">Web Development</h3>
					<p style="margin-bottom: 15px;">Build powerful, scalable web applications with cutting-edge technologies. We specialize in custom development that meets your exact requirements.</p>
					<ul style="list-style: none; padding: 0;">
						<li style="padding: 8px 0; border-bottom: 1px solid #F8F9FA;">✓ Custom WordPress Development</li>
						<li style="padding: 8px 0; border-bottom: 1px solid #F8F9FA;">✓ E-Commerce Solutions</li>
						<li style="padding: 8px 0; border-bottom: 1px solid #F8F9FA;">✓ Web Applications</li>
						<li style="padding: 8px 0;">✓ API Development</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Pricing Packages -->
<div style="padding: 60px 20px; background: #F8F9FA;">
	<div style="max-width: 1200px; margin: 0 auto;">
		<div style="text-align: center; margin-bottom: 50px;">
			<h2 style="font-size: 2.5rem; margin-bottom: 15px;">Service Packages</h2>
			<p style="color: #636E72;">Choose the package that fits your needs</p>
		</div>

		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
			<!-- Starter Package -->
			<div style="background: white; padding: 40px 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); text-align: center;">
				<h3 style="margin-bottom: 20px;">Starter</h3>
				<div style="font-size: 3rem; font-weight: 700; color: #6C5CE7; margin: 20px 0;">$2,999</div>
				<p style="color: #636E72; margin-bottom: 30px;">Perfect for small businesses and startups</p>
				<ul style="list-style: none; padding: 0; text-align: left; margin-bottom: 30px;">
					<li style="padding: 12px 0;">✓ 5-Page Website</li>
					<li style="padding: 12px 0;">✓ Responsive Design</li>
					<li style="padding: 12px 0;">✓ Basic SEO</li>
					<li style="padding: 12px 0;">✓ Contact Form</li>
					<li style="padding: 12px 0;">✓ 30 Days Support</li>
				</ul>
				<a href="/contact-us" style="display: block; padding: 15px; background: #6C5CE7; color: white; border-radius: 8px; text-decoration: none; font-weight: 600;">Get Started</a>
			</div>

			<!-- Professional Package -->
			<div style="background: white; padding: 40px 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); text-align: center; border: 3px solid #6C5CE7; position: relative;">
				<div style="position: absolute; top: -15px; left: 50%; transform: translateX(-50%); background: #6C5CE7; color: white; padding: 8px 20px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">MOST POPULAR</div>
				<h3 style="margin-bottom: 20px; margin-top: 10px;">Professional</h3>
				<div style="font-size: 3rem; font-weight: 700; color: #6C5CE7; margin: 20px 0;">$5,999</div>
				<p style="color: #636E72; margin-bottom: 30px;">Ideal for growing businesses</p>
				<ul style="list-style: none; padding: 0; text-align: left; margin-bottom: 30px;">
					<li style="padding: 12px 0;">✓ 10-Page Website</li>
					<li style="padding: 12px 0;">✓ Custom Design</li>
					<li style="padding: 12px 0;">✓ Advanced SEO</li>
					<li style="padding: 12px 0;">✓ CMS Integration</li>
					<li style="padding: 12px 0;">✓ E-Commerce Ready</li>
					<li style="padding: 12px 0;">✓ 90 Days Support</li>
				</ul>
				<a href="/contact-us" style="display: block; padding: 15px; background: #6C5CE7; color: white; border-radius: 8px; text-decoration: none; font-weight: 600;">Get Started</a>
			</div>

			<!-- Enterprise Package -->
			<div style="background: white; padding: 40px 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); text-align: center;">
				<h3 style="margin-bottom: 20px;">Enterprise</h3>
				<div style="font-size: 3rem; font-weight: 700; color: #6C5CE7; margin: 20px 0;">$15,999+</div>
				<p style="color: #636E72; margin-bottom: 30px;">For large-scale projects</p>
				<ul style="list-style: none; padding: 0; text-align: left; margin-bottom: 30px;">
					<li style="padding: 12px 0;">✓ Unlimited Pages</li>
					<li style="padding: 12px 0;">✓ Custom Development</li>
					<li style="padding: 12px 0;">✓ Premium SEO</li>
					<li style="padding: 12px 0;">✓ API Integration</li>
					<li style="padding: 12px 0;">✓ Advanced Features</li>
					<li style="padding: 12px 0;">✓ 1 Year Support</li>
				</ul>
				<a href="/contact-us" style="display: block; padding: 15px; background: #6C5CE7; color: white; border-radius: 8px; text-decoration: none; font-weight: 600;">Contact Us</a>
			</div>
		</div>
	</div>
</div>

<!-- CTA -->
<div style="padding: 80px 20px; text-align: center; background: #6C5CE7; color: white;">
	<h2 style="font-size: 2.5rem; margin-bottom: 20px; color: white;">Ready to Get Started?</h2>
	<p style="font-size: 1.25rem; margin-bottom: 30px; opacity: 0.95;">Let\'s discuss your project requirements</p>
	<a href="/contact-us" style="display: inline-block; padding: 15px 40px; background: white; color: #6C5CE7; border-radius: 8px; text-decoration: none; font-weight: 600;">Request a Quote</a>
</div>
';
}

/**
 * Portfolio Page Content
 */
function velocity_get_portfolio_page_content() {
	return '
<!-- Hero -->
<div style="background: linear-gradient(135deg, #6C5CE7 0%, #00B894 100%); color: white; text-align: center; padding: 80px 20px; margin-bottom: 60px;">
	<h1 style="font-size: 3rem; color: white; margin-bottom: 15px;">Our Portfolio</h1>
	<p style="font-size: 1.25rem; opacity: 0.95;">Explore our work and see how we help businesses succeed</p>
</div>

<!-- Portfolio Grid -->
<div style="padding: 60px 20px;">
	<div style="max-width: 1200px; margin: 0 auto;">
		<div style="text-align: center; margin-bottom: 50px;">
			<h2 style="font-size: 2.5rem; margin-bottom: 15px;">Featured Projects</h2>
			<p style="color: #636E72;">A showcase of our recent work across various industries</p>
		</div>

		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px;">
			<div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08); transition: transform 0.3s;">
				<img src="https://source.unsplash.com/800x600/?saas,dashboard" alt="TechFlow SaaS" style="width: 100%; height: 250px; object-fit: cover;" />
				<div style="padding: 30px;">
					<span style="display: inline-block; background: #6C5CE7; color: white; padding: 6px 15px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; margin-bottom: 15px;">WEB DEVELOPMENT</span>
					<h3 style="margin-bottom: 15px;">TechFlow SaaS Platform</h3>
					<p style="color: #636E72; margin-bottom: 20px;">Enterprise SaaS platform with real-time analytics and team collaboration features.</p>
					<a href="#" style="color: #6C5CE7; font-weight: 600; text-decoration: none;">View Case Study →</a>
				</div>
			</div>

			<div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
				<img src="https://source.unsplash.com/800x600/?restaurant,food" alt="Urban Eats" style="width: 100%; height: 250px; object-fit: cover;" />
				<div style="padding: 30px;">
					<span style="display: inline-block; background: #6C5CE7; color: white; padding: 6px 15px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; margin-bottom: 15px;">WEB DESIGN</span>
					<h3 style="margin-bottom: 15px;">Urban Eats Restaurant</h3>
					<p style="color: #636E72; margin-bottom: 20px;">Modern restaurant website with online ordering and reservation system.</p>
					<a href="#" style="color: #6C5CE7; font-weight: 600; text-decoration: none;">View Case Study →</a>
				</div>
			</div>

			<div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
				<img src="https://source.unsplash.com/800x600/?fitness,gym" alt="FitLife" style="width: 100%; height: 250px; object-fit: cover;" />
				<div style="padding: 30px;">
					<span style="display: inline-block; background: #6C5CE7; color: white; padding: 6px 15px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; margin-bottom: 15px;">MOBILE APP</span>
					<h3 style="margin-bottom: 15px;">FitLife Wellness App</h3>
					<p style="color: #636E72; margin-bottom: 20px;">Fitness tracking app with workout plans and nutrition guidance.</p>
					<a href="#" style="color: #6C5CE7; font-weight: 600; text-decoration: none;">View Case Study →</a>
				</div>
			</div>

			<div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
				<img src="https://source.unsplash.com/800x600/?fashion,shopping" alt="Luxe Fashion" style="width: 100%; height: 250px; object-fit: cover;" />
				<div style="padding: 30px;">
					<span style="display: inline-block; background: #6C5CE7; color: white; padding: 6px 15px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; margin-bottom: 15px;">E-COMMERCE</span>
					<h3 style="margin-bottom: 15px;">Luxe Fashion Store</h3>
					<p style="color: #636E72; margin-bottom: 20px;">Premium fashion e-commerce with advanced filtering and wishlist features.</p>
					<a href="#" style="color: #6C5CE7; font-weight: 600; text-decoration: none;">View Case Study →</a>
				</div>
			</div>

			<div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
				<img src="https://source.unsplash.com/800x600/?realestate,property" alt="Summit Real Estate" style="width: 100%; height: 250px; object-fit: cover;" />
				<div style="padding: 30px;">
					<span style="display: inline-block; background: #6C5CE7; color: white; padding: 6px 15px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; margin-bottom: 15px;">WEB DEVELOPMENT</span>
					<h3 style="margin-bottom: 15px;">Summit Real Estate</h3>
					<p style="color: #636E72; margin-bottom: 20px;">Property listing platform with virtual tours and mortgage calculator.</p>
					<a href="#" style="color: #6C5CE7; font-weight: 600; text-decoration: none;">View Case Study →</a>
				</div>
			</div>

			<div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
				<img src="https://source.unsplash.com/800x600/?organic,nature" alt="GreenLeaf Organics" style="width: 100%; height: 250px; object-fit: cover;" />
				<div style="padding: 30px;">
					<span style="display: inline-block; background: #6C5CE7; color: white; padding: 6px 15px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; margin-bottom: 15px;">BRANDING</span>
					<h3 style="margin-bottom: 15px;">GreenLeaf Organics</h3>
					<p style="color: #636E72; margin-bottom: 20px;">Complete brand identity for organic food company including logo and packaging.</p>
					<a href="#" style="color: #6C5CE7; font-weight: 600; text-decoration: none;">View Case Study →</a>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Testimonials -->
<div style="padding: 60px 20px; background: #F8F9FA;">
	<div style="max-width: 1200px; margin: 0 auto;">
		<div style="text-align: center; margin-bottom: 50px;">
			<h2 style="font-size: 2.5rem; margin-bottom: 15px;">What Clients Say</h2>
			<p style="color: #636E72;">Real feedback from real clients</p>
		</div>

		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px;">
			<div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
				<div style="font-size: 3rem; color: #6C5CE7; line-height: 1; margin-bottom: 20px;">"</div>
				<p style="font-style: italic; color: #636E72; margin-bottom: 25px;">Working with this team was an absolute pleasure. They delivered beyond our expectations and the platform they built has transformed our business.</p>
				<div style="display: flex; align-items: center; gap: 15px;">
					<img src="https://source.unsplash.com/100x100/?portrait,woman,ceo" alt="Sarah Mitchell" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;" />
					<div>
						<h5 style="margin: 0; font-weight: 700;">Sarah Mitchell</h5>
						<p style="margin: 0; font-size: 0.875rem; color: #636E72;">TechFlow CEO</p>
					</div>
				</div>
			</div>

			<div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
				<div style="font-size: 3rem; color: #6C5CE7; line-height: 1; margin-bottom: 20px;">"</div>
				<p style="font-style: italic; color: #636E72; margin-bottom: 25px;">The website they designed for us is stunning and has significantly increased our online orders. Customer support is exceptional.</p>
				<div style="display: flex; align-items: center; gap: 15px;">
					<img src="https://source.unsplash.com/100x100/?portrait,man,business" alt="John Anderson" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;" />
					<div>
						<h5 style="margin: 0; font-weight: 700;">John Anderson</h5>
						<p style="margin: 0; font-size: 0.875rem; color: #636E72;">Urban Eats Owner</p>
					</div>
				</div>
			</div>

			<div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
				<div style="font-size: 3rem; color: #6C5CE7; line-height: 1; margin-bottom: 20px;">"</div>
				<p style="font-style: italic; color: #636E72; margin-bottom: 25px;">From concept to launch, they were professional and attentive. Our app has received incredible feedback from users.</p>
				<div style="display: flex; align-items: center; gap: 15px;">
					<img src="https://source.unsplash.com/100x100/?portrait,woman,founder" alt="Emily Chen" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;" />
					<div>
						<h5 style="margin: 0; font-weight: 700;">Emily Chen</h5>
						<p style="margin: 0; font-size: 0.875rem; color: #636E72;">FitLife Founder</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Results -->
<div style="padding: 60px 20px; background: #6C5CE7; color: white; text-align: center;">
	<div style="max-width: 1200px; margin: 0 auto;">
		<h2 style="font-size: 2.5rem; margin-bottom: 15px; color: white;">Proven Results</h2>
		<p style="margin-bottom: 50px; opacity: 0.95;">Numbers that speak for themselves</p>

		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px;">
			<div>
				<h3 style="color: #FDCB6E; font-size: 3.5rem; margin-bottom: 10px;">500+</h3>
				<p>Projects Delivered</p>
			</div>
			<div>
				<h3 style="color: #FDCB6E; font-size: 3.5rem; margin-bottom: 10px;">98%</h3>
				<p>Client Satisfaction</p>
			</div>
			<div>
				<h3 style="color: #FDCB6E; font-size: 3.5rem; margin-bottom: 10px;">250%</h3>
				<p>Avg. ROI Increase</p>
			</div>
			<div>
				<h3 style="color: #FDCB6E; font-size: 3.5rem; margin-bottom: 10px;">24/7</h3>
				<p>Support Available</p>
			</div>
		</div>
	</div>
</div>

<!-- CTA -->
<div style="padding: 80px 20px; text-align: center;">
	<h2 style="font-size: 2.5rem; margin-bottom: 20px;">Ready to Start Your Success Story?</h2>
	<p style="font-size: 1.25rem; color: #636E72; margin-bottom: 30px;">Let\'s create something amazing together</p>
	<a href="/contact-us" style="display: inline-block; padding: 15px 40px; background: #6C5CE7; color: white; border-radius: 8px; text-decoration: none; font-weight: 600;">Start Your Project</a>
</div>
';
}

/**
 * Contact Page Content
 */
function velocity_get_contact_page_content() {
	return '
<!-- Hero -->
<div style="background: linear-gradient(135deg, #6C5CE7 0%, #00B894 100%); color: white; text-align: center; padding: 80px 20px; margin-bottom: 60px;">
	<h1 style="font-size: 3rem; color: white; margin-bottom: 15px;">Get in Touch</h1>
	<p style="font-size: 1.25rem; opacity: 0.95;">Let\'s discuss your project and bring your ideas to life</p>
</div>

<!-- Contact Section -->
<div style="padding: 60px 20px;">
	<div style="max-width: 1200px; margin: 0 auto;">
		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 60px;">
			<!-- Contact Info -->
			<div>
				<h2 style="margin-bottom: 20px;">Let\'s Talk</h2>
				<p style="margin-bottom: 40px; color: #636E72;">Have a project in mind? We\'d love to hear from you. Send us a message and we\'ll respond as soon as possible.</p>

				<div style="margin-bottom: 30px; display: flex; gap: 20px;">
					<div style="background: #6C5CE7; color: white; width: 50px; height: 50px; border-radius: 50%; display: grid; place-items: center; flex-shrink: 0;">
						<span style="font-size: 24px;">✉</span>
					</div>
					<div>
						<h4 style="margin-bottom: 8px;">Email Us</h4>
						<p style="margin: 0; color: #636E72;">hello@velocity.com</p>
						<p style="margin: 0; color: #636E72;">support@velocity.com</p>
					</div>
				</div>

				<div style="margin-bottom: 30px; display: flex; gap: 20px;">
					<div style="background: #00B894; color: white; width: 50px; height: 50px; border-radius: 50%; display: grid; place-items: center; flex-shrink: 0;">
						<span style="font-size: 24px;">☎</span>
					</div>
					<div>
						<h4 style="margin-bottom: 8px;">Call Us</h4>
						<p style="margin: 0; color: #636E72;">+1 (555) 123-4567</p>
						<p style="margin: 0; color: #636E72;">+1 (555) 987-6543</p>
					</div>
				</div>

				<div style="margin-bottom: 30px; display: flex; gap: 20px;">
					<div style="background: #FDCB6E; color: #2D3436; width: 50px; height: 50px; border-radius: 50%; display: grid; place-items: center; flex-shrink: 0;">
						<span style="font-size: 24px;">📍</span>
					</div>
					<div>
						<h4 style="margin-bottom: 8px;">Visit Us</h4>
						<p style="margin: 0; color: #636E72;">123 Innovation Street</p>
						<p style="margin: 0; color: #636E72;">San Francisco, CA 94102</p>
					</div>
				</div>

				<div style="margin-top: 40px;">
					<h4 style="margin-bottom: 15px;">Business Hours</h4>
					<p style="color: #636E72; line-height: 1.8; margin: 0;">
						Monday - Friday: 9:00 AM - 6:00 PM<br>
						Saturday: 10:00 AM - 4:00 PM<br>
						Sunday: Closed
					</p>
				</div>
			</div>

			<!-- Contact Form -->
			<div style="background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
				<h3 style="margin-bottom: 25px;">Send Us a Message</h3>

				<form method="post" action="#">
					<div style="margin-bottom: 20px;">
						<label style="display: block; margin-bottom: 8px; font-weight: 600;">Your Name <span style="color: #6C5CE7;">*</span></label>
						<input type="text" name="name" required style="width: 100%; padding: 12px; border: 2px solid #F8F9FA; border-radius: 8px; font-size: 1rem; font-family: inherit;" />
					</div>

					<div style="margin-bottom: 20px;">
						<label style="display: block; margin-bottom: 8px; font-weight: 600;">Your Email <span style="color: #6C5CE7;">*</span></label>
						<input type="email" name="email" required style="width: 100%; padding: 12px; border: 2px solid #F8F9FA; border-radius: 8px; font-size: 1rem; font-family: inherit;" />
					</div>

					<div style="margin-bottom: 20px;">
						<label style="display: block; margin-bottom: 8px; font-weight: 600;">Subject <span style="color: #6C5CE7;">*</span></label>
						<input type="text" name="subject" required style="width: 100%; padding: 12px; border: 2px solid #F8F9FA; border-radius: 8px; font-size: 1rem; font-family: inherit;" />
					</div>

					<div style="margin-bottom: 20px;">
						<label style="display: block; margin-bottom: 8px; font-weight: 600;">Message <span style="color: #6C5CE7;">*</span></label>
						<textarea name="message" rows="6" required style="width: 100%; padding: 12px; border: 2px solid #F8F9FA; border-radius: 8px; font-size: 1rem; resize: vertical; font-family: inherit;"></textarea>
					</div>

					<button type="submit" style="width: 100%; padding: 15px; background: #6C5CE7; color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer;">Send Message</button>
				</form>
			</div>
		</div>
	</div>
</div>

<!-- Map -->
<div style="padding: 60px 20px; background: #F8F9FA;">
	<div style="max-width: 1200px; margin: 0 auto; text-align: center;">
		<h2 style="font-size: 2.5rem; margin-bottom: 40px;">Find Us on the Map</h2>
		<div style="width: 100%; height: 400px; border-radius: 8px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
			<img src="https://source.unsplash.com/1600x900/?map,city" alt="Map Location" style="width: 100%; height: 100%; object-fit: cover;" />
		</div>
		<div style="margin-top: 30px;">
			<a href="https://maps.google.com" target="_blank" rel="noopener noreferrer" style="display: inline-block; padding: 15px 40px; background: #6C5CE7; color: white; border-radius: 8px; text-decoration: none; font-weight: 600;">Get Directions</a>
		</div>
	</div>
</div>

<!-- FAQ -->
<div style="padding: 60px 20px;">
	<div style="max-width: 1200px; margin: 0 auto;">
		<div style="text-align: center; margin-bottom: 50px;">
			<h2 style="font-size: 2.5rem; margin-bottom: 15px;">Frequently Asked Questions</h2>
			<p style="color: #636E72;">Quick answers to common questions</p>
		</div>

		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 30px;">
			<div>
				<h4 style="margin-bottom: 12px;">How long does a typical project take?</h4>
				<p style="color: #636E72;">Project timelines vary based on complexity. A typical website takes 4-8 weeks, while larger applications may take 3-6 months. We\'ll provide a detailed timeline during consultation.</p>
			</div>

			<div>
				<h4 style="margin-bottom: 12px;">What is your pricing structure?</h4>
				<p style="color: #636E72;">We offer flexible pricing based on project scope. We can work with fixed-price contracts, hourly rates, or retainer agreements. Contact us for a custom quote.</p>
			</div>

			<div>
				<h4 style="margin-bottom: 12px;">Do you provide ongoing support?</h4>
				<p style="color: #636E72;">Yes! We offer various support and maintenance packages to ensure your project continues to perform optimally after launch.</p>
			</div>

			<div>
				<h4 style="margin-bottom: 12px;">Can you work with existing codebases?</h4>
				<p style="color: #636E72;">Absolutely. We can enhance, maintain, or completely redesign existing projects. We\'re experienced with various technologies and platforms.</p>
			</div>
		</div>
	</div>
</div>
';
}
